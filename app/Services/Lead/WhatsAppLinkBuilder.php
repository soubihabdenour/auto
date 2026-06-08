<?php

declare(strict_types=1);

namespace App\Services\Lead;

use App\Services\Phone;

final class WhatsAppLinkBuilder
{
    public function __construct(
        private string $businessNumber,
        /** @var array<string,string> locale => prefix message */
        private array  $defaultMessages,
    ) {}

    /**
     * Build a wa.me URL with a prefilled message tied to a specific vehicle.
     */
    public function forVehicle(
        string $locale,
        ?string $vehicleTitle = null,
        ?string $vehicleUrl   = null,
    ): string {
        $number  = Phone::forWhatsapp($this->businessNumber);
        $prefix  = $this->defaultMessages[$locale] ?? ($this->defaultMessages['en'] ?? '');
        $parts   = [trim($prefix)];
        if ($vehicleTitle !== null) {
            $parts[] = $vehicleTitle;
        }
        if ($vehicleUrl !== null) {
            $parts[] = $vehicleUrl;
        }
        $message = implode(' — ', array_filter($parts, fn ($s) => $s !== ''));
        return 'https://wa.me/' . rawurlencode($number) . '?text=' . rawurlencode($message);
    }

    public function generic(string $locale): string
    {
        $number = Phone::forWhatsapp($this->businessNumber);
        $message = $this->defaultMessages[$locale] ?? ($this->defaultMessages['en'] ?? '');
        return 'https://wa.me/' . rawurlencode($number) . '?text=' . rawurlencode($message);
    }
}
