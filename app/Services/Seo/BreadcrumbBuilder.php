<?php

declare(strict_types=1);

namespace App\Services\Seo;

/**
 * Builds schema.org/BreadcrumbList JSON-LD from an ordered array of
 * { name, url } items. The view inlines the result via json_encode in
 * a <script type="application/ld+json"> tag.
 */
final class BreadcrumbBuilder
{
    /**
     * @param array<int,array{name:string,url:string}> $items
     * @return array<string,mixed>
     */
    public static function build(array $items): array
    {
        $listItems = [];
        foreach (array_values($items) as $i => $it) {
            $listItems[] = [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $it['name'],
                'item'     => $it['url'],
            ];
        }
        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $listItems,
        ];
    }
}
