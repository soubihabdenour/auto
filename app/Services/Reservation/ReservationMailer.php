<?php

declare(strict_types=1);

namespace App\Services\Reservation;

use App\Services\Mailer\MailerInterface;
use App\Services\Setting\SettingService;

/**
 * Notification e-mails around the reservation lifecycle.
 * Bodies are inlined here (no separate Blade-like templates) to mirror
 * how LeadService renders its admin notification.
 */
final class ReservationMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private SettingService  $settings,
    ) {}

    /**
     * @param array<string, mixed> $reservation  the freshly-created row (with reference, expires_at, etc.)
     * @param array<string, mixed> $vehicle      raw vehicle row (id, slug, brand_name, model_name, year, etc.)
     */
    public function pendingCreated(array $reservation, array $vehicle, string $publicStatusUrl): void
    {
        $this->notifyAdminOfPending($reservation, $vehicle, $publicStatusUrl);
        $this->notifyCustomerInstructions($reservation, $vehicle, $publicStatusUrl);
    }

    /** @param array<string, mixed> $reservation */
    public function confirmed(array $reservation, ?string $publicStatusUrl): void
    {
        if (empty($reservation['email'])) return;
        $ref     = (string) $reservation['reference'];
        $vehicle = (string) ($reservation['vehicle_label'] ?? '');
        $subject = "[KAE] Reservation {$ref} confirmed";
        $body  = '<h2>Your deposit has been received.</h2>';
        $body .= '<p>Reference: <strong>' . htmlspecialchars($ref) . '</strong></p>';
        if ($vehicle !== '') $body .= '<p>Vehicle: ' . htmlspecialchars($vehicle) . '</p>';
        $body .= '<p>This vehicle is now locked for you. We will contact you for next steps.</p>';
        if ($publicStatusUrl) {
            $body .= '<p><a href="' . htmlspecialchars($publicStatusUrl) . '">Track your reservation</a></p>';
        }
        $this->mailer->send((string) $reservation['email'], $subject, $body);
    }

    /** @param array<string, mixed> $reservation */
    public function expired(array $reservation, ?string $publicStatusUrl): void
    {
        if (empty($reservation['email'])) return;
        $ref     = (string) $reservation['reference'];
        $vehicle = (string) ($reservation['vehicle_label'] ?? '');
        $subject = "[KAE] Reservation {$ref} expired";
        $body  = '<h2>Your reservation has expired.</h2>';
        $body .= '<p>Reference: <strong>' . htmlspecialchars($ref) . '</strong></p>';
        if ($vehicle !== '') $body .= '<p>Vehicle: ' . htmlspecialchars($vehicle) . '</p>';
        $body .= '<p>We did not receive your deposit within the time window. The vehicle is now available again. You are welcome to start a new reservation if you still want it.</p>';
        if ($publicStatusUrl) {
            $body .= '<p><a href="' . htmlspecialchars($publicStatusUrl) . '">View reservation</a></p>';
        }
        $this->mailer->send((string) $reservation['email'], $subject, $body);
    }

    /** @param array<string, mixed> $reservation */
    public function cancelled(array $reservation, ?string $publicStatusUrl): void
    {
        if (empty($reservation['email'])) return;
        $ref     = (string) $reservation['reference'];
        $reason  = (string) ($reservation['cancellation_reason'] ?? '');
        $subject = "[KAE] Reservation {$ref} cancelled";
        $body  = '<h2>Your reservation has been cancelled.</h2>';
        $body .= '<p>Reference: <strong>' . htmlspecialchars($ref) . '</strong></p>';
        if ($reason !== '') $body .= '<p>Reason: ' . htmlspecialchars($reason) . '</p>';
        $body .= '<p>Please contact us if you believe this is in error.</p>';
        if ($publicStatusUrl) {
            $body .= '<p><a href="' . htmlspecialchars($publicStatusUrl) . '">View reservation</a></p>';
        }
        $this->mailer->send((string) $reservation['email'], $subject, $body);
    }

    /**
     * @param array<string, mixed> $reservation
     * @param array<string, mixed> $vehicle
     */
    private function notifyAdminOfPending(array $reservation, array $vehicle, string $publicStatusUrl): void
    {
        $to = (string) $this->settings->get('reservation_admin_notification_email', '');
        if ($to === '') {
            // Fall back to the general lead notification address so it lands somewhere.
            $to = (string) $this->settings->get('lead_notification_email', '');
        }
        if ($to === '') return;

        $ref = (string) $reservation['reference'];
        $subject = sprintf('[KAE] New reservation %s — pending deposit', $ref);
        $rows = [
            'Reference' => $ref,
            'Vehicle'   => trim(($vehicle['brand_name'] ?? '') . ' ' . ($vehicle['model_name'] ?? '') . ' ' . ($vehicle['year'] ?? '')),
            'Customer'  => (string) $reservation['name'],
            'Phone'     => (string) $reservation['phone'],
            'WhatsApp'  => (string) ($reservation['whatsapp'] ?? '—'),
            'Email'     => (string) ($reservation['email']    ?? '—'),
            'City'      => (string) ($reservation['city']     ?? '—'),
            'Deposit'   => sprintf('$%s', number_format((float) $reservation['deposit_amount_usd'], 2)),
            'Expires'   => (string) $reservation['expires_at'],
            'Status'    => $publicStatusUrl,
        ];

        $html = '<h2>New reservation pending deposit</h2><table>';
        foreach ($rows as $k => $v) {
            $html .= '<tr><th align="left">' . htmlspecialchars($k) . '</th><td>'
                   . htmlspecialchars((string) $v) . '</td></tr>';
        }
        $html .= '</table>';
        $html .= '<p>Mark the deposit received from the admin panel once the wire arrives.</p>';

        $this->mailer->send($to, $subject, $html);
    }

    /**
     * @param array<string, mixed> $reservation
     * @param array<string, mixed> $vehicle
     */
    private function notifyCustomerInstructions(array $reservation, array $vehicle, string $publicStatusUrl): void
    {
        if (empty($reservation['email'])) return;
        $ref         = (string) $reservation['reference'];
        $depositUsd  = (float)  $reservation['deposit_amount_usd'];
        $expiresAt   = (string) $reservation['expires_at'];
        $locale      = (string) ($reservation['locale'] ?? 'en');
        $instructions = (string) $this->settings->get('reservation_bank_instructions_' . $locale, '');
        if ($instructions === '') {
            $instructions = (string) $this->settings->get('reservation_bank_instructions_en', '');
        }

        $subject = "[KAE] Reservation {$ref} — payment instructions";
        $vehicleLabel = trim(($vehicle['brand_name'] ?? '') . ' ' . ($vehicle['model_name'] ?? '') . ' ' . ($vehicle['year'] ?? ''));

        $html  = '<h2>Thank you — your reservation is pending.</h2>';
        $html .= '<p>Reference: <strong>' . htmlspecialchars($ref) . '</strong></p>';
        if ($vehicleLabel !== '') {
            $html .= '<p>Vehicle: ' . htmlspecialchars($vehicleLabel) . '</p>';
        }
        $html .= '<p>Deposit due: <strong>$' . number_format($depositUsd, 2) . '</strong></p>';
        $html .= '<p>Please send the deposit before <strong>' . htmlspecialchars($expiresAt) . '</strong> using the instructions below, then reply to this email or message us on WhatsApp with the proof.</p>';
        if ($instructions !== '') {
            $html .= '<hr><div>' . nl2br(htmlspecialchars($instructions)) . '</div><hr>';
        }
        $html .= '<p><a href="' . htmlspecialchars($publicStatusUrl) . '">Track your reservation</a></p>';

        $this->mailer->send((string) $reservation['email'], $subject, $html);
    }
}
