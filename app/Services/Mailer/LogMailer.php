<?php

declare(strict_types=1);

namespace App\Services\Mailer;

/**
 * Writes outgoing mail to storage/logs/mail.log instead of sending.
 * Used in dev + as the SMTP fallback before production credentials are set.
 */
final class LogMailer implements MailerInterface
{
    public function __construct(private string $logPath) {}

    public function send(
        string|array $to,
        string $subject,
        string $htmlBody,
        ?string $textBody = null,
        array $cc = [],
        array $bcc = [],
    ): bool {
        $entry = sprintf(
            "[%s] TO=%s CC=%s BCC=%s\nSUBJECT: %s\nHTML:\n%s\nTEXT:\n%s\n%s\n",
            date('c'),
            is_array($to) ? implode(',', $to) : $to,
            implode(',', $cc),
            implode(',', $bcc),
            $subject,
            $htmlBody,
            $textBody ?? '(none)',
            str_repeat('─', 80)
        );
        return (bool) @file_put_contents($this->logPath, $entry, FILE_APPEND | LOCK_EX);
    }
}
