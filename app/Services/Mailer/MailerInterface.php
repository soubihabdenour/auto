<?php

declare(strict_types=1);

namespace App\Services\Mailer;

interface MailerInterface
{
    /**
     * @param string|array<int,string> $to
     * @param array<int,string>        $cc
     * @param array<int,string>        $bcc
     */
    public function send(
        string|array $to,
        string $subject,
        string $htmlBody,
        ?string $textBody = null,
        array $cc = [],
        array $bcc = [],
    ): bool;
}
