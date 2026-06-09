<?php

use App\Core\Router;
use App\Controllers\Admin\AuthController as AdminAuth;
use App\Controllers\Admin\DashboardController as AdminDashboard;
use App\Controllers\Admin\LeadController as AdminLead;
use App\Controllers\Admin\SettingController as AdminSetting;
use App\Controllers\Admin\ReservationController as AdminReservation;
use App\Controllers\Admin\TestimonialController as AdminTestimonial;
use App\Controllers\Admin\VehicleController as AdminVehicle;
use App\Controllers\Admin\VehicleImageController as AdminVehicleImage;
use App\Controllers\Public\HomeController;
use App\Controllers\Public\InquiryController;
use App\Controllers\Public\PageController;
use App\Controllers\Public\ReservationController;
use App\Controllers\Public\VehicleController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\LocaleMiddleware;
use App\Middleware\RateLimitMiddleware;

return function (Router $router): void {

    // ----- ROOT + utility -------------------------------------------
    $router->get('/', [HomeController::class, 'redirectToDefaultLocale']);

    // Locale-less event ping (sendBeacon can't set CSRF; rate-limited instead)
    $router->post('/events/whatsapp', [InquiryController::class, 'whatsappEvent'])
           ->middleware(RateLimitMiddleware::class);

    // SEO endpoints
    $router->get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');
    $router->get('/robots.txt',  [PageController::class, 'robots'])->name('robots');

    // ----- ADMIN  ---------------------------------------------------
    // Registered BEFORE the localized group so /admin doesn't accidentally
    // match /{locale}.
    $router->get('/admin/login',  [AdminAuth::class, 'showLogin']);
    $router->post('/admin/login', [AdminAuth::class, 'login'])
           ->middleware(CsrfMiddleware::class, RateLimitMiddleware::class);
    $router->post('/admin/logout',[AdminAuth::class, 'logout'])
           ->middleware(CsrfMiddleware::class);

    $router->group([
        'prefix'     => '/admin',
        'middleware' => [AuthMiddleware::class, CsrfMiddleware::class],
    ], function (Router $router) {
        $router->get('', [AdminDashboard::class, 'index'])->name('admin.dashboard');
        $router->get('/regulations', [AdminDashboard::class, 'regulations'])->name('admin.regulations');

        // Vehicles
        $router->get('/vehicles',                 [AdminVehicle::class, 'index'])->name('admin.vehicles.index');
        $router->get('/vehicles/create',          [AdminVehicle::class, 'create'])->name('admin.vehicles.create');
        $router->post('/vehicles',                [AdminVehicle::class, 'store'])->name('admin.vehicles.store');
        $router->post('/vehicles/decode-vin',     [AdminVehicle::class, 'decodeVin'])->name('admin.vehicles.decode-vin');
        $router->get('/vehicles/{id}/edit',       [AdminVehicle::class, 'edit'])->name('admin.vehicles.edit');
        $router->put('/vehicles/{id}',            [AdminVehicle::class, 'update'])->name('admin.vehicles.update');
        $router->delete('/vehicles/{id}',         [AdminVehicle::class, 'destroy'])->name('admin.vehicles.destroy');
        // Image management
        $router->post('/vehicles/{id}/images',         [AdminVehicleImage::class, 'upload']);
        $router->post('/vehicles/{id}/images/cover',   [AdminVehicleImage::class, 'setCover']);
        $router->delete('/vehicles/images/{imageId}',  [AdminVehicleImage::class, 'destroy']);

        // Reservations
        $router->get('/reservations',                   [AdminReservation::class, 'index'])->name('admin.reservations.index');
        $router->get('/reservations/{id}',              [AdminReservation::class, 'show'])->name('admin.reservations.show');
        $router->post('/reservations/{id}/confirm',     [AdminReservation::class, 'confirm'])->name('admin.reservations.confirm');
        $router->post('/reservations/{id}/cancel',      [AdminReservation::class, 'cancel'])->name('admin.reservations.cancel');
        $router->post('/reservations/{id}/convert',     [AdminReservation::class, 'convert'])->name('admin.reservations.convert');
        $router->post('/reservations/{id}/note',        [AdminReservation::class, 'addNote'])->name('admin.reservations.note');

        // Leads
        $router->get('/leads',                    [AdminLead::class, 'index'])->name('admin.leads.index');
        $router->get('/leads/export',             [AdminLead::class, 'exportCsv'])->name('admin.leads.export');
        $router->get('/leads/{id}',               [AdminLead::class, 'show'])->name('admin.leads.show');
        $router->put('/leads/{id}/status',        [AdminLead::class, 'updateStatus'])->name('admin.leads.status');
        $router->post('/leads/{id}/notes',        [AdminLead::class, 'addNote'])->name('admin.leads.notes');

        // Testimonials
        $router->get('/testimonials',             [AdminTestimonial::class, 'index'])->name('admin.testimonials.index');
        $router->get('/testimonials/create',      [AdminTestimonial::class, 'create'])->name('admin.testimonials.create');
        $router->post('/testimonials',            [AdminTestimonial::class, 'store'])->name('admin.testimonials.store');
        $router->get('/testimonials/{id}/edit',   [AdminTestimonial::class, 'edit'])->name('admin.testimonials.edit');
        $router->put('/testimonials/{id}',        [AdminTestimonial::class, 'update'])->name('admin.testimonials.update');
        $router->delete('/testimonials/{id}',     [AdminTestimonial::class, 'destroy'])->name('admin.testimonials.destroy');

        // Settings
        $router->get('/settings',                 [AdminSetting::class, 'index'])->name('admin.settings.index');
        $router->put('/settings',                 [AdminSetting::class, 'update'])->name('admin.settings.update');
    });

    // ----- PUBLIC (localized) ---------------------------------------
    $router->group([
        'prefix'     => '/{locale}',
        'middleware' => [LocaleMiddleware::class, CsrfMiddleware::class],
    ], function (Router $router) {
        $router->get('/',                  [HomeController::class, 'index'])->name('home');
        $router->get('/why-korea',         [PageController::class, 'whyKorea'])->name('why-korea');
        $router->get('/import-process',    [PageController::class, 'importProcess'])->name('import-process');
        $router->get('/cost-calculator',   [PageController::class, 'costCalculator'])->name('cost-calculator');
        $router->get('/testimonials',      [PageController::class, 'testimonials'])->name('testimonials');
        $router->get('/about',             [PageController::class, 'about'])->name('about');
        $router->get('/contact',           [PageController::class, 'contact'])->name('contact');
        $router->get('/request-vehicle',   [PageController::class, 'requestVehicle'])->name('request-vehicle');
        $router->get('/privacy',           [PageController::class, 'privacy'])->name('privacy');
        $router->get('/terms',             [PageController::class, 'terms'])->name('terms');

        // Vehicles
        $router->get('/vehicles',          [VehicleController::class, 'index'])->name('vehicles.index');
        $router->get('/vehicles/filter',   [VehicleController::class, 'filter'])->name('vehicles.filter');
        $router->get('/vehicles/{slug}',   [VehicleController::class, 'show'])->name('vehicles.show');

        // Reservations (off-platform deposit flow)
        $router->get('/vehicles/{slug}/reserve',  [ReservationController::class, 'create'])->name('reservations.create');
        $router->post('/vehicles/{slug}/reserve', [ReservationController::class, 'store'])
               ->middleware(RateLimitMiddleware::class)->name('reservations.store');
        $router->get('/reservations/{reference}', [ReservationController::class, 'show'])->name('reservations.show');

        // Lead submission (rate-limited per IP/route)
        $router->post('/inquiry',          [InquiryController::class, 'store'])
               ->middleware(RateLimitMiddleware::class)->name('inquiry.store');
        $router->post('/contact',          [InquiryController::class, 'storeContact'])
               ->middleware(RateLimitMiddleware::class)->name('contact.store');
        $router->post('/request-vehicle',  [InquiryController::class, 'storeRequest'])
               ->middleware(RateLimitMiddleware::class)->name('request.store');

        // Lead success
        $router->get('/lead/success',      [InquiryController::class, 'success'])->name('lead.success');
    });

    // sitemap.xml, robots.txt — Phase 4
};
