<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Repositories\LeadRepository;
use App\Repositories\VehicleRepository;
use App\Services\Lead\LeadFormRules;
use App\Services\Lead\LeadService;
use App\Services\Lead\RequestVehicleMessageFormatter;
use App\Services\Lead\WhatsAppLinkBuilder;

final class InquiryController
{
    public function __construct(
        private View                $view,
        private LeadService         $leadService,
        private LeadRepository      $leadRepo,
        private VehicleRepository   $vehicles,
        private WhatsAppLinkBuilder $whatsapp,
        private Session             $session,
    ) {}

    /**
     * Vehicle-tied lead from the detail-page modal.
     * Body: name, phone, whatsapp?, city?, message?, vehicle_id, lead_type, _website (honeypot)
     */
    public function store(Request $request): Response
    {
        $locale = current_locale();

        // Honeypot — discard silently, pretend success.
        if (! empty($request->input('_website'))) {
            return $this->redirectSuccess(null);
        }

        $validator = new Validator($request->post, LeadFormRules::vehicleInquiry());
        if (! $validator->passes()) {
            return $this->backWithErrors($request, $validator->errors());
        }

        $vehicleId = (int) ($request->input('vehicle_id') ?: 0) ?: null;

        try {
            $leadId = $this->leadService->record(
                input:        $validator->validated(),
                request:      $request,
                locale:       $locale,
                leadType:     (string) $request->input('lead_type'),
                vehicleId:    $vehicleId,
                vehicleTitle: null,
            );
        } catch (\Throwable) {
            return $this->backWithErrors($request, ['_global' => [t('lead.errors.invalid')]]);
        }

        return $this->redirectSuccess($vehicleId, $leadId);
    }

    /**
     * Generic "I want to source a vehicle" submission (no specific vehicle).
     */
    public function storeRequest(Request $request): Response
    {
        $locale  = current_locale();
        $backUrl = locale_url('/request-vehicle');

        if (! empty($request->input('_website'))) {
            $this->session->flash('request_success', t('pages.request_vehicle.success'));
            return Response::redirect($backUrl);
        }

        $validator = new Validator($request->post, LeadFormRules::requestVehicle());
        if (! $validator->passes()) {
            return $this->flashErrorsTo($backUrl, $request, $validator->errors());
        }

        $data = $validator->validated();

        try {
            $this->leadService->record(
                input: [
                    'name'     => $data['name'],
                    'phone'    => $data['phone'],
                    'whatsapp' => $data['whatsapp'] ?? null,
                    'city'     => $data['city'] ?? null,
                    'message'  => RequestVehicleMessageFormatter::format($data),
                ],
                request:      $request,
                locale:       $locale,
                leadType:     'inquiry',
                vehicleId:    null,
                vehicleTitle: null,
            );
        } catch (\Throwable) {
            return $this->flashErrorsTo($backUrl, $request, ['_global' => [t('lead.errors.invalid')]]);
        }

        $this->session->flash('request_success', t('pages.request_vehicle.success'));
        return Response::redirect($backUrl);
    }

    /**
     * Contact-form submission.
     */
    public function storeContact(Request $request): Response
    {
        $locale  = current_locale();
        $backUrl = locale_url('/contact');

        if (! empty($request->input('_website'))) {
            $this->session->flash('contact_success', t('pages.contact.success'));
            return Response::redirect($backUrl);
        }

        $validator = new Validator($request->post, LeadFormRules::contact());
        if (! $validator->passes()) {
            return $this->flashErrorsTo($backUrl, $request, $validator->errors());
        }

        try {
            $this->leadService->record(
                input:    $validator->validated(),
                request:  $request,
                locale:   $locale,
                leadType: 'inquiry',
            );
        } catch (\Throwable) {
            return $this->flashErrorsTo($backUrl, $request, ['_global' => [t('lead.errors.invalid')]]);
        }

        $this->session->flash('contact_success', t('pages.contact.success'));
        return Response::redirect($backUrl);
    }

    /**
     * WhatsApp click tracking via navigator.sendBeacon.
     * No CSRF (beacon can't send tokens reliably); rate-limited at the
     * middleware layer if needed.
     */
    public function whatsappEvent(Request $request): Response
    {
        $payload = json_decode(file_get_contents('php://input') ?: '[]', true);
        $vehicleId = isset($payload['vehicle_id']) && is_numeric($payload['vehicle_id'])
            ? (int) $payload['vehicle_id']
            : null;

        try {
            $this->leadRepo->logWhatsappClick(
                vehicleId: $vehicleId,
                locale:    current_locale(),
                ipHash:    hash('sha256', $request->ip()),
                uaHash:    hash('sha256', $request->userAgent()),
            );
        } catch (\Throwable) {
            // best-effort
        }
        return Response::json(['ok' => true]);
    }

    public function success(Request $request): Response
    {
        $locale    = current_locale();
        $leadId    = (int) ($request->query['lead'] ?? 0);
        $vehicleId = (int) ($request->query['vehicle'] ?? 0);

        $waLink = $this->whatsapp->generic($locale);
        if ($vehicleId > 0) {
            try {
                $row = $this->vehicles->findBySlug((string) ($request->query['slug'] ?? ''), $locale);
                if ($row) {
                    $waLink = $this->whatsapp->forVehicle(
                        $locale,
                        (string) $row['title'],
                        url(locale_url('/vehicles/' . $row['slug']))
                    );
                }
            } catch (\Throwable) {}
        }

        $html = $this->view->render('public/lead/success', [
            'page_title' => t('lead.success.title') . ' · ' . t('common.brand.name'),
            'lead_id'    => $leadId,
            'wa_link'    => $waLink,
        ]);
        return Response::html($html);
    }

    // ---------- Private helpers ----------

    private function redirectSuccess(?int $vehicleId, ?int $leadId = null): Response
    {
        $url    = locale_url('/lead/success');
        $params = [];
        if ($leadId)    $params['lead']    = $leadId;
        if ($vehicleId) $params['vehicle'] = $vehicleId;
        if ($params) {
            $url .= '?' . http_build_query($params);
        }
        return Response::redirect($url);
    }

    /**
     * Flash old input + errors, then bounce back to the referer (vehicle modal)
     * or fall back to the vehicles index.
     *
     * @param array<string, string[]> $errors
     */
    private function backWithErrors(Request $request, array $errors): Response
    {
        $this->session->flash('_old', $request->post);
        $this->session->flash('_errors', $errors);

        $referer = $request->header('Referer');
        if ($referer && str_contains($referer, $request->server['HTTP_HOST'] ?? '')) {
            return Response::redirect($referer);
        }
        return Response::redirect(locale_url('/vehicles'));
    }

    /**
     * Flash old input + errors, then redirect to a known page (contact / request).
     *
     * @param array<string, string[]> $errors
     */
    private function flashErrorsTo(string $url, Request $request, array $errors): Response
    {
        $this->session->flash('_old', $request->post);
        $this->session->flash('_errors', $errors);
        return Response::redirect($url);
    }
}
