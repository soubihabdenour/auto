<?php

declare(strict_types=1);

namespace App\Services\Reservation;

use App\Core\Database;
use App\Core\Request;
use App\Repositories\ReservationRepository;
use App\Repositories\VehicleRepository;
use App\Services\Phone;
use App\Services\Setting\SettingService;
use RuntimeException;

/**
 * Owns the reservation state machine and keeps vehicle status in sync.
 *
 * Allowed transitions:
 *   (none)             → pending_deposit   [create]
 *   pending_deposit    → confirmed         [confirm]
 *   pending_deposit    → expired           [expireDue, cron-driven]
 *   pending_deposit    → cancelled         [cancel]
 *   confirmed          → cancelled         [cancel]
 *   confirmed          → converted         [convert, once the sale closes]
 *
 * Each transition flips the vehicle status inside the same DB transaction
 * so a partial failure never leaves a vehicle stuck in pending_reservation.
 */
final class ReservationService
{
    public function __construct(
        private ReservationRepository       $reservations,
        private VehicleRepository           $vehicles,
        private ReservationReferenceGenerator $refGen,
        private SettingService              $settings,
        private Database                    $db,
    ) {}

    /**
     * Create a reservation tied to a vehicle. The vehicle must be in 'available'
     * status and have no other active reservation.
     *
     * @param  array<string, mixed> $input   validated form data
     * @return array{id:int, reference:string, expires_at:string, deposit_amount_usd:float}
     */
    public function create(
        int     $vehicleId,
        array   $input,
        Request $request,
        string  $locale,
        ?int    $leadId = null,
    ): array {
        $vehicle = $this->vehicles->findRawById($vehicleId);
        if ($vehicle === null) {
            throw new RuntimeException('Vehicle not found.');
        }
        if ($vehicle['status'] !== 'available') {
            throw new RuntimeException('Vehicle is not available for reservation.');
        }
        if ($this->reservations->activeForVehicle($vehicleId) !== null) {
            throw new RuntimeException('This vehicle already has an active reservation.');
        }

        $depositUsd  = (float) $this->settings->get('reservation_default_deposit_usd', 500);
        $expiryHours = max(1, (int) $this->settings->get('reservation_expiry_hours', 48));
        $expiresAt   = (new \DateTimeImmutable('+' . $expiryHours . ' hours'))->format('Y-m-d H:i:s');
        $reference   = $this->refGen->next();

        $payload = [
            'reference'          => $reference,
            'vehicle_id'         => $vehicleId,
            'lead_id'            => $leadId,
            'name'               => trim((string) ($input['name']  ?? '')),
            'phone'              => Phone::normalize((string) ($input['phone'] ?? '')),
            'whatsapp'           => isset($input['whatsapp']) && $input['whatsapp'] !== ''
                                       ? Phone::normalize((string) $input['whatsapp'])
                                       : null,
            'email'              => isset($input['email']) && $input['email'] !== ''
                                       ? trim((string) $input['email']) : null,
            'city'               => isset($input['city']) ? trim((string) $input['city']) : null,
            'deposit_amount_usd' => $depositUsd,
            'currency'           => 'USD',
            'status'             => 'pending_deposit',
            'expires_at'         => $expiresAt,
            'locale'             => $locale,
            'ip_hash'            => hash('sha256', $request->ip()),
            'user_agent'         => substr($request->userAgent(), 0, 255),
        ];

        $this->db->beginTransaction();
        try {
            $id = $this->reservations->create($payload);
            $this->vehicles->setStatus($vehicleId, 'pending_reservation');
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }

        return [
            'id'                 => $id,
            'reference'          => $reference,
            'expires_at'         => $expiresAt,
            'deposit_amount_usd' => $depositUsd,
        ];
    }

    /**
     * Admin marks the deposit as received. Vehicle moves to 'reserved'.
     */
    public function confirm(int $reservationId, int $adminUserId, ?string $adminNote = null): void
    {
        $r = $this->mustFind($reservationId);
        $this->assertStatus($r, ['pending_deposit'], 'confirm');

        $this->db->beginTransaction();
        try {
            $this->reservations->markConfirmed($reservationId, $adminUserId, $adminNote);
            $this->vehicles->setStatus((int) $r['vehicle_id'], 'reserved');
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Admin cancels — vehicle returns to 'available'. Works from both
     * pending_deposit and confirmed (e.g. customer backs out post-deposit).
     */
    public function cancel(int $reservationId, int $adminUserId, ?string $reason = null): void
    {
        $r = $this->mustFind($reservationId);
        $this->assertStatus($r, ['pending_deposit', 'confirmed'], 'cancel');

        $this->db->beginTransaction();
        try {
            $this->reservations->markCancelled($reservationId, $adminUserId, $reason);
            $this->vehicles->setStatus((int) $r['vehicle_id'], 'available');
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Transition a confirmed reservation to 'converted' once the sale closes.
     * Vehicle moves to 'sold'.
     */
    public function convertToSale(int $reservationId): void
    {
        $r = $this->mustFind($reservationId);
        $this->assertStatus($r, ['confirmed'], 'convert');

        $this->db->beginTransaction();
        try {
            $this->reservations->markConverted($reservationId);
            $this->vehicles->setStatus((int) $r['vehicle_id'], 'sold');
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Cron entry-point: expire all overdue pending_deposit reservations and
     * return their vehicles to 'available'. Returns the IDs that were expired
     * so the caller can fire customer notifications.
     *
     * @return array<int, array{id:int, vehicle_id:int, reference:string, email:?string, locale:string}>
     */
    public function expireDue(?string $nowDatetime = null): array
    {
        $now  = $nowDatetime ?? date('Y-m-d H:i:s');
        $rows = $this->reservations->fetchExpired($now);
        if ($rows === []) {
            return [];
        }

        $this->db->beginTransaction();
        try {
            $ids = array_map(static fn ($r): int => (int) $r['id'], $rows);
            $this->reservations->markIdsExpired($ids);
            foreach ($rows as $r) {
                $this->vehicles->setStatus((int) $r['vehicle_id'], 'available');
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }

        return array_map(static fn ($r): array => [
            'id'         => (int) $r['id'],
            'vehicle_id' => (int) $r['vehicle_id'],
            'reference'  => (string) $r['reference'],
            'email'      => $r['email'] !== null ? (string) $r['email'] : null,
            'locale'     => (string) $r['locale'],
        ], $rows);
    }

    /** @return array<string, mixed> */
    private function mustFind(int $id): array
    {
        $row = $this->reservations->findById($id);
        if ($row === null) {
            throw new RuntimeException("Reservation {$id} not found.");
        }
        return $row;
    }

    /**
     * @param array<string, mixed> $r
     * @param list<string>         $allowed
     */
    private function assertStatus(array $r, array $allowed, string $action): void
    {
        if (! in_array($r['status'], $allowed, true)) {
            throw new RuntimeException(
                "Cannot {$action} reservation in status '{$r['status']}'."
            );
        }
    }
}
