<?php

declare(strict_types=1);

namespace App\Services\Lead;

use App\Core\Request;
use App\Repositories\LeadRepository;
use App\Services\Mailer\MailerInterface;
use App\Services\Phone;
use App\Services\Setting\SettingService;

/**
 * Encapsulates the "a visitor submitted a lead" use case:
 *   - normalize input
 *   - persist
 *   - send admin notification email
 *
 * Returns the new lead ID.
 */
final class LeadService
{
    public function __construct(
        private LeadRepository  $leads,
        private MailerInterface $mailer,
        private SettingService  $settings,
    ) {}

    /** @param array<string, mixed> $input  validated form data */
    public function record(
        array $input,
        Request $request,
        string $locale,
        string $leadType = 'inquiry',
        ?int $vehicleId = null,
        ?string $vehicleTitle = null,
    ): int {
        $data = [
            'vehicle_id'   => $vehicleId,
            'name'         => trim((string) ($input['name']  ?? '')),
            'phone'        => Phone::normalize((string) ($input['phone']    ?? '')),
            'whatsapp'     => isset($input['whatsapp']) && $input['whatsapp'] !== ''
                                ? Phone::normalize((string) $input['whatsapp'])
                                : null,
            'email'        => isset($input['email']) && $input['email'] !== ''
                                ? trim((string) $input['email'])
                                : null,
            'country'      => trim((string) ($input['country'] ?? 'Algeria')),
            'city'         => isset($input['city']) ? trim((string) $input['city']) : null,
            'message'      => isset($input['message']) ? trim((string) $input['message']) : null,
            'lead_type'    => $leadType,
            'status'       => 'new',
            'source'       => $vehicleId ? 'vehicle_page' : 'direct',
            'locale'       => $locale,
            'ip_hash'      => hash('sha256', $request->ip()),
            'user_agent'   => substr($request->userAgent(), 0, 255),
            'referrer'     => substr((string) $request->header('Referer', ''), 0, 500),
            'utm_source'   => $input['utm_source']   ?? null,
            'utm_medium'   => $input['utm_medium']   ?? null,
            'utm_campaign' => $input['utm_campaign'] ?? null,
        ];

        $id = $this->leads->create($data);
        $this->notifyAdmin($id, $data, $vehicleTitle);
        return $id;
    }

    /** @param array<string, mixed> $data */
    private function notifyAdmin(int $leadId, array $data, ?string $vehicleTitle): void
    {
        $to = (string) $this->settings->get('lead_notification_email', '');
        if ($to === '') {
            return; // nowhere to send
        }
        $subject = sprintf(
            '[KAE] New %s lead #%d — %s',
            $data['lead_type'],
            $leadId,
            $data['name'] ?: 'Anonymous'
        );

        $rows = [
            'Type'        => $data['lead_type'],
            'Name'        => $data['name'],
            'Phone'       => $data['phone'],
            'WhatsApp'    => $data['whatsapp'] ?? '—',
            'Email'       => $data['email']    ?? '—',
            'City'        => $data['city']     ?? '—',
            'Locale'      => $data['locale'],
            'Vehicle'     => $vehicleTitle ?? '—',
            'Message'     => $data['message']  ?? '—',
            'Source'      => $data['source'],
            'Referrer'    => $data['referrer'] ?: '—',
        ];

        $html = '<h2>New lead #' . htmlspecialchars((string) $leadId) . '</h2><table>';
        foreach ($rows as $k => $v) {
            $html .= '<tr><th align="left">' . htmlspecialchars($k) . '</th><td>'
                  .  htmlspecialchars((string) $v) . '</td></tr>';
        }
        $html .= '</table>';

        $this->mailer->send($to, $subject, $html);
    }
}
