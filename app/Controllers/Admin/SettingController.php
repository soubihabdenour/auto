<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\SettingRepository;
use App\Services\Auth\AuthService;
use App\Services\Setting\SettingService;

/**
 * Settings editor. Settings are organised on the public side into groups;
 * here we expose the editable keys grouped by concern.
 */
final class SettingController
{
    private const GROUPS = [
        'General' => ['site_name', 'default_locale'],
        'Contact' => [
            'contact_email', 'contact_phone', 'whatsapp_number',
            'lead_notification_email',
        ],
        'Brand taglines' => [
            'site_tagline_ar', 'site_tagline_fr', 'site_tagline_en',
        ],
        'WhatsApp prefill' => [
            'whatsapp_default_message_ar', 'whatsapp_default_message_fr',
            'whatsapp_default_message_en',
        ],
        'Cost estimator' => [
            'estimator_shipping_base_usd', 'estimator_customs_rate',
            'estimator_tva_rate', 'estimator_service_fee_flat_usd',
            'estimator_service_fee_percent', 'fx_usd_to_dzd',
        ],
        'Reservations' => [
            'reservation_default_deposit_usd',
            'reservation_expiry_hours',
            'reservation_admin_notification_email',
            'reservation_bank_instructions_ar',
            'reservation_bank_instructions_fr',
            'reservation_bank_instructions_en',
        ],
        'Social' => ['social_facebook', 'social_instagram', 'social_tiktok'],
        'Analytics' => [
            'analytics_plausible_domain',
            'analytics_ga4_id',
            'search_console_verification',
        ],
    ];

    public function __construct(
        private View              $view,
        private SettingRepository $repo,
        private SettingService    $service,
        private AuthService       $auth,
        private Session           $session,
    ) {}

    public function index(Request $request): Response
    {
        $all = $this->safe(fn () => $this->repo->all());
        $values = [];
        foreach ($all as $r) $values[(string) $r['key']] = ['value' => $r['value'], 'type' => $r['type']];
        return Response::html($this->view->render('admin/settings/index', [
            'page_title' => 'Settings · Admin',
            'groups'     => self::GROUPS,
            'values'     => $values,
        ]));
    }

    public function update(Request $request): Response
    {
        $touched = 0;
        foreach (self::GROUPS as $keys) {
            foreach ($keys as $key) {
                if (! array_key_exists($key, $request->post)) continue;
                try {
                    $this->repo->set($key, (string) $request->post[$key]);
                    $touched++;
                } catch (\Throwable $e) {
                    $this->session->flash('_errors', ['_global' => [$e->getMessage()]]);
                    return Response::redirect('/admin/settings');
                }
            }
        }
        $this->auth->writeAudit(
            $this->currentUserId(), 'settings.update', 'settings', null, $request->ip(), ['keys_touched' => $touched]
        );
        $this->session->flash('flash', "Settings saved ({$touched} keys updated).");
        return Response::redirect('/admin/settings');
    }

    private function currentUserId(): ?int
    {
        $u = $this->auth->user();
        return $u ? (int) $u['id'] : null;
    }

    private function safe(\Closure $cb, mixed $fallback = []): mixed
    {
        try { return $cb(); }
        catch (\Throwable) { return $fallback; }
    }
}
