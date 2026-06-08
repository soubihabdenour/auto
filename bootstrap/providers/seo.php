<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Container;
use App\Core\Database;
use App\Services\Seo\OrganizationSchemaBuilder;
use App\Services\Seo\SitemapGenerator;
use App\Services\Seo\VehicleSchemaBuilder;
use App\Services\Setting\SettingService;

return function (Container $container, Config $config): void {
    $container->singleton(SitemapGenerator::class, function (Container $c) use ($config): SitemapGenerator {
        return new SitemapGenerator(
            db:        $c->get(Database::class),
            siteUrl:   (string) $config->get('app.url', ''),
            locales:   (array)  $config->get('locales.available', ['ar','fr','en']),
            cachePath: BASE_PATH . '/storage/cache/sitemap.xml',
        );
    });

    $container->singleton(OrganizationSchemaBuilder::class, function (Container $c) use ($config): OrganizationSchemaBuilder {
        $s = $c->get(SettingService::class);
        $siteUrl = (string) $config->get('app.url', '');
        return new OrganizationSchemaBuilder(
            siteName: (string) ($s->get('site_name', $config->get('app.name'))),
            siteUrl:  $siteUrl,
            logoUrl:  $siteUrl . '/assets/img/logo.svg',
            phone:    (string) $s->get('contact_phone', '') ?: null,
            email:    (string) $s->get('contact_email', '') ?: null,
            sameAs:   array_filter([
                (string) $s->get('social_facebook', ''),
                (string) $s->get('social_instagram', ''),
                (string) $s->get('social_tiktok', ''),
            ]),
        );
    });

    $container->singleton(VehicleSchemaBuilder::class, function (Container $c) use ($config): VehicleSchemaBuilder {
        return new VehicleSchemaBuilder(
            siteName: (string) ($c->get(SettingService::class)->get('site_name', $config->get('app.name'))),
            siteUrl:  (string) $config->get('app.url', ''),
        );
    });
};
