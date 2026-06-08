<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Exception\NotFoundException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Repositories\BodyTypeRepository;
use App\Repositories\BrandRepository;
use App\Repositories\ModelRepository;
use App\Repositories\VehicleRepository;
use App\Services\Auth\AuthService;
use App\Services\Storage\StorageInterface;
use App\Services\Vehicle\VehicleAdminWriter;
use App\Services\Vehicle\VehicleFormRules;

final class VehicleController
{
    public function __construct(
        private View               $view,
        private VehicleRepository  $vehicles,
        private BrandRepository    $brands,
        private ModelRepository    $models,
        private BodyTypeRepository $bodyTypes,
        private Session            $session,
        private AuthService        $auth,
        private StorageInterface   $storage,
        private VehicleAdminWriter $writer,
    ) {}

    public function index(Request $request): Response
    {
        $filters = [
            'status'   => (string) $request->input('status', ''),
            'brand_id' => (int)    $request->input('brand_id', 0),
            'q'        => (string) $request->input('q', ''),
            'featured' => (bool)   $request->input('featured', false),
        ];
        $page    = max(1, (int) $request->input('page', 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        $rows  = $this->safe(fn () => $this->vehicles->adminList($filters, $perPage, $offset));
        $total = $this->safe(fn () => $this->vehicles->adminCount($filters), 0);

        return Response::html($this->view->render('admin/vehicles/index', [
            'page_title' => 'Vehicles · Admin',
            'rows'       => $rows,
            'total'      => $total,
            'page'       => $page,
            'pages'      => max(1, (int) ceil($total / $perPage)),
            'filters'    => $filters,
            'brands'     => $this->safe(fn () => $this->brands->allActive()),
            'statuses'   => VehicleFormRules::STATUSES,
        ]));
    }

    public function create(Request $request): Response
    {
        return $this->renderForm(null, $request);
    }

    public function store(Request $request): Response
    {
        $errors = $this->validatePayload($request->post);
        if ($errors !== []) {
            return $this->backToFormWithErrors('/admin/vehicles/create', $request->post, $errors);
        }

        $data = $this->writer->buildDataPayload($request->post, null, $this->currentUserId());
        try {
            $id = $this->vehicles->create($data);
            $this->writer->upsertTranslations($id, $request->post);
            $this->writer->maybeUpsertInspection($id, $request->post);
        } catch (\Throwable $e) {
            return $this->backToFormWithErrors(
                '/admin/vehicles/create',
                $request->post,
                ['_global' => ['Could not save: ' . $e->getMessage()]],
            );
        }

        $this->auth->writeAudit(
            $this->currentUserId(), 'vehicle.create', 'vehicle', $id, $request->ip()
        );
        $this->session->flash('flash', 'Vehicle created. Add photos in the Media tab.');
        return Response::redirect('/admin/vehicles/' . $id . '/edit#media');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->route('id', 0);
        $row = $this->vehicles->findRawById($id)
            ?? throw new NotFoundException("Vehicle {$id} not found");
        return $this->renderForm($row, $request);
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->route('id', 0);
        $existing = $this->vehicles->findRawById($id);
        if ($existing === null) throw new NotFoundException();

        $errors = $this->validatePayload($request->post);
        if ($errors !== []) {
            return $this->backToFormWithErrors('/admin/vehicles/' . $id . '/edit', $request->post, $errors);
        }

        $data = $this->writer->buildDataPayload($request->post, $existing, $this->currentUserId());
        try {
            $this->vehicles->update($id, $data);
            $this->writer->upsertTranslations($id, $request->post);
            $this->writer->maybeUpsertInspection($id, $request->post);
        } catch (\Throwable $e) {
            return $this->backToFormWithErrors(
                '/admin/vehicles/' . $id . '/edit',
                $request->post,
                ['_global' => ['Could not save: ' . $e->getMessage()]],
            );
        }

        $this->auth->writeAudit(
            $this->currentUserId(), 'vehicle.update', 'vehicle', $id, $request->ip()
        );
        $this->session->flash('flash', 'Saved.');
        return Response::redirect('/admin/vehicles/' . $id . '/edit');
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->route('id', 0);
        $row = $this->vehicles->findRawById($id);
        if ($row === null) throw new NotFoundException();

        // Soft archive by default; ?hard=1 deletes the row outright.
        if ((string) $request->input('hard') === '1') {
            try {
                // Delete on-disk images too (best-effort)
                foreach ($this->vehicles->imagesFor($id) as $img) {
                    $this->storage->delete((string) $img['path']);
                }
                $this->vehicles->delete($id);
                $this->auth->writeAudit(
                    $this->currentUserId(), 'vehicle.delete', 'vehicle', $id, $request->ip()
                );
                $this->session->flash('flash', 'Vehicle deleted.');
            } catch (\Throwable $e) {
                $this->session->flash('_errors', ['_global' => ['Delete failed: ' . $e->getMessage()]]);
                return Response::redirect('/admin/vehicles/' . $id . '/edit');
            }
            return Response::redirect('/admin/vehicles');
        }

        $this->vehicles->setStatus($id, 'archived');
        $this->auth->writeAudit(
            $this->currentUserId(), 'vehicle.archive', 'vehicle', $id, $request->ip()
        );
        $this->session->flash('flash', 'Vehicle archived. Public pages will return 404.');
        return Response::redirect('/admin/vehicles');
    }

    // ---------- Private helpers ----------

    private function renderForm(?array $vehicle, Request $request): Response
    {
        $vid = $vehicle['id'] ?? null;
        $translations = $vid ? $this->vehicles->translationsFor((int) $vid) : [];
        $byLocale = [];
        foreach ($translations as $tr) $byLocale[$tr['locale']] = $tr;
        $inspection = $vid ? $this->vehicles->inspectionFor((int) $vid) : null;
        $images     = $vid ? $this->vehicles->imagesFor((int) $vid) : [];

        return Response::html($this->view->render('admin/vehicles/form', [
            'page_title'   => ($vid ? 'Edit' : 'New') . ' vehicle · Admin',
            'vehicle'      => $vehicle,
            'translations' => $byLocale,
            'inspection'   => $inspection,
            'images'       => $images,
            'old'          => flash('_old') ?? [],
            'errors'       => flash('_errors') ?? [],
            'brands'       => $this->safe(fn () => $this->brands->allActive()),
            'models'       => $this->safe(fn () => $this->models->allActive()),
            'body_types'   => $this->safe(fn () => $this->bodyTypes->all('en')),
            'statuses'     => VehicleFormRules::STATUSES,
            'fuel_types'   => VehicleFormRules::FUEL_TYPES,
            'transmissions'=> VehicleFormRules::TRANSMISSIONS,
            'drivetrains'  => VehicleFormRules::DRIVETRAINS,
            'accident'     => VehicleFormRules::ACCIDENT,
            'locales'      => (array) config('locales.available', ['ar','fr','en']),
        ]));
    }

    /** @return array<string, string[]> */
    private function validatePayload(array $data): array
    {
        $v = new Validator($data, VehicleFormRules::validatorRules());
        return $v->passes() ? [] : $v->errors();
    }

    /**
     * Flash old input + errors and redirect back to the form.
     * @param array<string, mixed>    $old
     * @param array<string, string[]> $errors
     */
    private function backToFormWithErrors(string $url, array $old, array $errors): Response
    {
        $this->session->flash('_old', $old);
        $this->session->flash('_errors', $errors);
        return Response::redirect($url);
    }

    private function currentUserId(): ?int
    {
        $u = $this->auth->user();
        return $u ? (int) $u['id'] : null;
    }

    private function safe(\Closure $cb, mixed $fallback = []): mixed
    {
        try { return $cb(); }
        catch (\Throwable) { return $fallback; }
    }
}
