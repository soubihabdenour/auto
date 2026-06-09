<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Exception\NotFoundException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Repositories\ReservationRepository;
use App\Repositories\VehicleRepository;
use App\Services\Reservation\ReservationMailer;
use App\Services\Reservation\ReservationService;
use App\Services\Setting\SettingService;

final class ReservationController
{
    public function __construct(
        private View                  $view,
        private VehicleRepository     $vehicles,
        private ReservationRepository $reservations,
        private ReservationService    $service,
        private ReservationMailer     $mailer,
        private SettingService        $settings,
        private Session               $session,
    ) {}

    /**
     * Pre-reservation form on the vehicle (locale + slug). Shows deposit
     * amount, expiry hours, and per-locale bank instructions.
     */
    public function create(Request $request): Response
    {
        $locale  = current_locale();
        $slug    = (string) $request->route('slug', '');
        $vehicle = $this->vehicles->findBySlug($slug, $locale)
            ?? throw new NotFoundException("Vehicle {$slug} not found");

        $active = $this->reservations->activeForVehicle((int) $vehicle['id']);

        return Response::html($this->view->render('public/reservations/create', [
            'page_title'          => t('reservation.create.title') . ' · ' . t('common.brand.name'),
            'vehicle'             => $vehicle,
            'active_reservation'  => $active,
            'deposit_amount_usd'  => (float) $this->settings->get('reservation_default_deposit_usd', 500),
            'expiry_hours'        => (int)   $this->settings->get('reservation_expiry_hours', 48),
            'bank_instructions'   => (string) $this->settings->get('reservation_bank_instructions_' . $locale, ''),
            'old'                 => flash('_old') ?? [],
            'errors'              => flash('_errors') ?? [],
        ]));
    }

    /**
     * Create a reservation. POST body: name, phone, whatsapp?, email?, city?,
     * agree_terms (checkbox), _website (honeypot).
     */
    public function store(Request $request): Response
    {
        $locale  = current_locale();
        $slug    = (string) $request->route('slug', '');
        $vehicle = $this->vehicles->findBySlug($slug, $locale)
            ?? throw new NotFoundException("Vehicle {$slug} not found");

        // Honeypot — discard silently, pretend success.
        if (! empty($request->input('_website'))) {
            return Response::redirect(locale_url('/vehicles/' . $slug));
        }

        $validator = new Validator($request->post, [
            'name'         => 'required|string|min:2|max:150',
            'phone'        => 'required|phone',
            'whatsapp'     => 'nullable|phone',
            'email'        => 'nullable|email|max:190',
            'city'         => 'nullable|string|max:120',
            'agree_terms'  => 'required',
        ]);

        if (! $validator->passes()) {
            return $this->backWithErrors($slug, $request, $validator->errors());
        }

        try {
            $result = $this->service->create(
                vehicleId: (int) $vehicle['id'],
                input:     $validator->validated(),
                request:   $request,
                locale:    $locale,
            );
        } catch (\Throwable $e) {
            return $this->backWithErrors($slug, $request, [
                '_global' => [$e->getMessage() ?: t('reservation.errors.invalid')],
            ]);
        }

        // Re-read the full row + vehicle to give the mailer something rich.
        $row = $this->reservations->findById($result['id']);
        if ($row !== null) {
            try {
                $statusUrl = url(locale_url('/reservations/' . $result['reference']));
                $this->mailer->pendingCreated($row, $vehicle, $statusUrl);
            } catch (\Throwable) {
                // mail is best-effort
            }
        }

        return Response::redirect(locale_url('/reservations/' . $result['reference']));
    }

    /**
     * Public status page — no auth, security comes from the random reference.
     */
    public function show(Request $request): Response
    {
        $reference = (string) $request->route('reference', '');
        $row = $this->reservations->findByReference($reference)
            ?? throw new NotFoundException("Reservation {$reference} not found");

        $locale = current_locale();
        $instructions = (string) $this->settings->get('reservation_bank_instructions_' . $locale, '');

        return Response::html($this->view->render('public/reservations/show', [
            'page_title'        => t('reservation.show.title') . ' · ' . t('common.brand.name'),
            'reservation'       => $row,
            'bank_instructions' => $instructions,
        ]));
    }

    /** @param array<string, string[]> $errors */
    private function backWithErrors(string $slug, Request $request, array $errors): Response
    {
        $this->session->flash('_old', $request->post);
        $this->session->flash('_errors', $errors);
        return Response::redirect(locale_url('/vehicles/' . $slug . '/reserve'));
    }
}
