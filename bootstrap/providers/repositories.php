<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Container;
use App\Core\Database;
use App\Repositories\BodyTypeRepository;
use App\Repositories\BrandRepository;
use App\Repositories\LeadRepository;
use App\Repositories\ModelRepository;
use App\Repositories\PageRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\SettingRepository;
use App\Repositories\TestimonialRepository;
use App\Repositories\VehicleRepository;

return function (Container $container, Config $config): void {
    $repositories = [
        BodyTypeRepository::class,
        BrandRepository::class,
        LeadRepository::class,
        ModelRepository::class,
        PageRepository::class,
        ReservationRepository::class,
        SettingRepository::class,
        TestimonialRepository::class,
        VehicleRepository::class,
    ];

    foreach ($repositories as $repoClass) {
        $container->singleton($repoClass, function (Container $c) use ($repoClass): object {
            return new $repoClass($c->get(Database::class));
        });
    }
};
