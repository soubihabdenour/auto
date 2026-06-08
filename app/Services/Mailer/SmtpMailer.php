<?php

declare(strict_types=1);

namespace App\Services\Mailer;

/**
 * Lightweight SMTP-via-mail() adapter. For real SMTP, swap in PHPMailer or
 * Symfony Mailer (composer require) and implement MailerInterface.
 * v1 uses PHP's mail() function — sufficient for low-volume lead alerts.
 */
final class SmtpMailer implements MailerInterface
{
    public function __construct(
        private string $fromAddress,
        private string $fromName,
    ) {}

    public function send(
        string|array $to,
        string $subject,
        string $htmlBody,
        ?string $textBody = null,
        array $cc = [],
        array $bcc = [],
    ): bool {
        $recipients = is_array($to) ? implode(',', $to) : $to;
        $boundary   = bin2hex(random_bytes(16));

        $headers   = [];
        $headers[] = 'From: ' . $this->encodeAddress($this->fromName, $this->fromAddress);
        $headers[] = 'Reply-To: ' . $this->fromAddress;
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
        if ($cc !== [])  { $headers[] = 'Cc: '  . implode(',', $cc); }
        if ($bcc !== []) { $headers[] = 'Bcc: ' . implode(',', $bcc); }

        $text = $textBody ?? strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $htmlBody));

        $body  = "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $text . "\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $htmlBody . "\r\n";
        $body .= "--{$boundary}--\r\n";

        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        return @mail($recipients, $encodedSubject, $body, implode("\r\n", $headers));
    }

    private function encodeAddress(string $name, string $email): string
    {
        $encoded = '=?UTF-8?B?' . base64_encode($name) . '?=';
        return "{$encoded} <{$email}>";
    }
}
