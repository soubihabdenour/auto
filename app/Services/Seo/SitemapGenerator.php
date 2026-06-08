<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Core\Database;

/**
 * Emits a urlset XML containing every public URL across every locale.
 * Caches to storage/cache/sitemap.xml; ttl is checked in the controller.
 */
final class SitemapGenerator
{
    /** @param array<int,string> $locales */
    public function __construct(
        private Database $db,
        private string   $siteUrl,
        private array    $locales,
        private string   $cachePath,
    ) {}

    public function generate(): string
    {
        $now  = date('c');
        $urls = [];

        // 1. Static pages × locales
        $statics = ['/', '/vehicles', '/why-korea', '/import-process',
                    '/testimonials', '/about', '/contact', '/request-vehicle'];
        foreach ($statics as $path) {
            $alternates = $this->alternatesFor($path);
            foreach ($this->locales as $loc) {
                $urls[] = [
                    'loc'        => $this->siteUrl . '/' . $loc . ($path === '/' ? '' : $path),
                    'lastmod'    => $now,
                    'changefreq' => $path === '/' ? 'daily' : 'weekly',
                    'priority'   => $path === '/' ? '1.0' : '0.7',
                    'alternates' => $alternates,
                ];
            }
        }

        // 2. Vehicle pages
        $vehicles = $this->safeSelectVehicles();
        foreach ($vehicles as $v) {
            $slug = (string) $v['slug'];
            $lastmod = ! empty($v['updated_at']) ? date('c', strtotime((string) $v['updated_at'])) : $now;
            $alternates = $this->alternatesFor('/vehicles/' . $slug);
            foreach ($this->locales as $loc) {
                $urls[] = [
                    'loc'        => $this->siteUrl . '/' . $loc . '/vehicles/' . $slug,
                    'lastmod'    => $lastmod,
                    'changefreq' => 'weekly',
                    'priority'   => '0.8',
                    'alternates' => $alternates,
                ];
            }
        }

        return $this->renderXml($urls);
    }

    public function generateAndCache(): string
    {
        $xml = $this->generate();
        if (! is_dir(dirname($this->cachePath))) {
            mkdir(dirname($this->cachePath), 0755, true);
        }
        @file_put_contents($this->cachePath, $xml, LOCK_EX);
        return $xml;
    }

    public function cachedXml(int $ttlSeconds = 3600): ?string
    {
        if (! is_file($this->cachePath)) return null;
        if (filemtime($this->cachePath) < time() - $ttlSeconds) return null;
        return (string) @file_get_contents($this->cachePath);
    }

    /** @return array<string,string> locale → URL */
    private function alternatesFor(string $path): array
    {
        $out = [];
        foreach ($this->locales as $loc) {
            $out[$loc] = $this->siteUrl . '/' . $loc . ($path === '/' ? '' : $path);
        }
        return $out;
    }

    /** @return array<int,array<string,mixed>> */
    private function safeSelectVehicles(): array
    {
        try {
            return $this->db->select(
                "SELECT slug, updated_at
                   FROM vehicles
                  WHERE status IN ('available','reserved','sold')
                  ORDER BY updated_at DESC"
            );
        } catch (\Throwable) {
            return [];
        }
    }

    /** @param array<int,array<string,mixed>> $urls */
    private function renderXml(array $urls): string
    {
        $out  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $out .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
        foreach ($urls as $u) {
            $out .= "  <url>\n";
            $out .= '    <loc>' . htmlspecialchars($u['loc']) . "</loc>\n";
            $out .= '    <lastmod>' . htmlspecialchars($u['lastmod']) . "</lastmod>\n";
            $out .= '    <changefreq>' . $u['changefreq'] . "</changefreq>\n";
            $out .= '    <priority>' . $u['priority'] . "</priority>\n";
            foreach (($u['alternates'] ?? []) as $loc => $alt) {
                $out .= '    <xhtml:link rel="alternate" hreflang="' . $loc . '" href="' . htmlspecialchars($alt) . "\"/>\n";
            }
            $out .= "  </url>\n";
        }
        $out .= '</urlset>' . "\n";
        return $out;
    }
}
