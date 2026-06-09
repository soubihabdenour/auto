<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Container;
use App\Core\Database;
use App\Core\Session;
use App\Repositories\LeadRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\SettingRepository;
use App\Repositories\VehicleRepository;
use App\Services\Auth\AuthService;
use App\Services\Estimate\ImportCostEstimator;
use App\Services\Image\ImageProcessor;
use App\Services\Lead\LeadService;
use App\Services\Lead\WhatsAppLinkBuilder;
use App\Services\Mailer\LogMailer;
use App\Services\Mailer\MailerInterface;
use App\Services\Mailer\SmtpMailer;
use App\Services\Reservation\ReservationMailer;
use App\Services\Reservation\ReservationReferenceGenerator;
use App\Services\Reservation\ReservationService;
use App\Services\Setting\SettingService;
use App\Services\Storage\LocalStorage;
use App\Services\Storage\StorageInterface;

return function (Container $container, Config $config): void {
    $container->singleton(AuthService::class, function (Container $c): AuthService {
        return new AuthService(
            $c->get(Database::class),
            $c->get(Session::class),
            maxAttempts:    (int) env('RATE_LIMIT_LOGIN_ATTEMPTS', 5),
            lockoutMinutes: (int) env('RATE_LIMIT_LOGIN_WINDOW_MIN', 15),
        );
    });

    $container->singleton(SettingService::class, function (Container $c): SettingService {
        return new SettingService($c->get(SettingRepository::class));
    });

    $container->singleton(MailerInterface::class, function () use ($config): MailerInterface {
        $driver = (string) env('MAIL_DRIVER', 'log');
        if ($driver === 'smtp') {
            return new SmtpMailer(
                fromAddress: (string) env('MAIL_FROM_ADDRESS', 'noreply@localhost'),
                fromName:    (string) env('MAIL_FROM_NAME',    (string) $config->get('app.name')),
            );
        }
        return new LogMailer(BASE_PATH . '/storage/logs/mail.log');
    });

    $container->singleton(LeadService::class, function (Container $c): LeadService {
        return new LeadService(
            $c->get(LeadRepository::class),
            $c->get(MailerInterface::class),
            $c->get(SettingService::class),
        );
    });

    $container->singleton(WhatsAppLinkBuilder::class, function (Container $c): WhatsAppLinkBuilder {
        $settings = $c->get(SettingService::class);
        return new WhatsAppLinkBuilder(
            businessNumber: (string) $settings->get('whatsapp_number', '+213000000000'),
            defaultMessages: [
                'ar' => (string) $settings->get('whatsapp_default_message_ar', 'مرحبا، أنا مهتم بـ:'),
                'fr' => (string) $settings->get('whatsapp_default_message_fr', 'Bonjour, je suis intéressé par:'),
                'en' => (string) $settings->get('whatsapp_default_message_en', 'Hello, I am interested in:'),
            ],
        );
    });

    $container->singleton(StorageInterface::class, function () use ($config): StorageInterface {
        return new LocalStorage(
            baseDir:   (string) $config->get('app.paths.uploads'),
            publicUrl: (string) env('STORAGE_PUBLIC_URL', '/uploads'),
        );
    });

    $container->singleton(ImageProcessor::class, function (Container $c): ImageProcessor {
        return new ImageProcessor($c->get(StorageInterface::class));
    });

    $container->singleton(ReservationReferenceGenerator::class, function (Container $c): ReservationReferenceGenerator {
        return new ReservationReferenceGenerator($c->get(ReservationRepository::class));
    });

    $container->singleton(ReservationMailer::class, function (Container $c): ReservationMailer {
        return new ReservationMailer(
            $c->get(MailerInterface::class),
            $c->get(SettingService::class),
        );
    });

    $container->singleton(ReservationService::class, function (Container $c): ReservationService {
        return new ReservationService(
            $c->get(ReservationRepository::class),
            $c->get(VehicleRepository::class),
            $c->get(ReservationReferenceGenerator::class),
            $c->get(SettingService::class),
            $c->get(Database::class),
        );
    });

    $container->singleton(ImportCostEstimator::class, function (Container $c): ImportCostEstimator {
        $s = $c->get(SettingService::class);
        return new ImportCostEstimator(
            shippingBaseUsd:    (float) $s->get('estimator_shipping_base_usd', 1500.0),
            customsRate:        (float) $s->get('estimator_customs_rate', 0.30),
            tvaRate:            (float) $s->get('estimator_tva_rate', 0.19),
            serviceFeeFlatUsd:  (float) $s->get('estimator_service_fee_flat_usd', 500.0),
            serviceFeePercent:  (float) $s->get('estimator_service_fee_percent', 0.02),
            fxUsdToDzd:         (float) $s->get('fx_usd_to_dzd', 135.0),
        );
    });
};
