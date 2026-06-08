<?php

declare(strict_types=1);

namespace App\Services\Seo;

/**
 * Site-wide schema.org/Organization JSON-LD. Emitted in the layout <head>.
 * Lets Google show a knowledge-panel for brand searches and surface our
 * contact / social URLs.
 */
final class OrganizationSchemaBuilder
{
    public function __construct(
        private string $siteName,
        private string $siteUrl,
        private string $logoUrl,
        private ?string $phone = null,
        private ?string $email = null,
        /** @var array<int,string> $sameAs */
        private array $sameAs = [],
    ) {}

    /** @return array<string,mixed> */
    public function build(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => $this->siteName,
            'url'      => $this->siteUrl,
            'logo'     => $this->logoUrl,
        ];
        if ($this->sameAs !== []) {
            $schema['sameAs'] = array_values(array_filter($this->sameAs));
        }
        if ($this->phone || $this->email) {
            $contactPoint = ['@type' => 'ContactPoint', 'contactType' => 'customer service'];
            if ($this->phone) $contactPoint['telephone'] = $this->phone;
            if ($this->email) $contactPoint['email']     = $this->email;
            $contactPoint['availableLanguage'] = ['Arabic', 'French', 'English'];
            $contactPoint['areaServed']        = 'DZ';
            $schema['contactPoint'] = $contactPoint;
        }
        return $schema;
    }
}
