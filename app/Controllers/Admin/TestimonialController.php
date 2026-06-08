<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Exception\NotFoundException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Repositories\TestimonialRepository;
use App\Services\Auth\AuthService;

final class TestimonialController
{
    public function __construct(
        private View                  $view,
        private TestimonialRepository $testimonials,
        private AuthService           $auth,
        private Session               $session,
    ) {}

    public function index(Request $request): Response
    {
        $rows = $this->safe(fn () => $this->testimonials->adminAll());
        return Response::html($this->view->render('admin/testimonials/index', [
            'page_title' => 'Testimonials · Admin',
            'rows'       => $rows,
        ]));
    }

    public function create(Request $request): Response { return $this->renderForm(null); }

    public function store(Request $request): Response
    {
        if (! $this->validateAndFlash($request->post)) {
            return Response::redirect('/admin/testimonials/create');
        }
        try {
            $id = $this->testimonials->create($request->post);
            $this->saveTranslations($id, $request->post);
            $this->auth->writeAudit($this->currentUserId(), 'testimonial.create', 'testimonial', $id, $request->ip());
            $this->session->flash('flash', 'Testimonial created.');
            return Response::redirect('/admin/testimonials/' . $id . '/edit');
        } catch (\Throwable $e) {
            $this->session->flash('_errors', ['_global' => [$e->getMessage()]]);
            $this->session->flash('_old', $request->post);
            return Response::redirect('/admin/testimonials/create');
        }
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->route('id', 0);
        $row = $this->testimonials->find($id) ?? throw new NotFoundException();
        return $this->renderForm($row);
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->route('id', 0);
        if ($this->testimonials->find($id) === null) throw new NotFoundException();
        if (! $this->validateAndFlash($request->post)) {
            return Response::redirect('/admin/testimonials/' . $id . '/edit');
        }
        try {
            $this->testimonials->update($id, $request->post);
            $this->saveTranslations($id, $request->post);
            $this->auth->writeAudit($this->currentUserId(), 'testimonial.update', 'testimonial', $id, $request->ip());
            $this->session->flash('flash', 'Saved.');
        } catch (\Throwable $e) {
            $this->session->flash('_errors', ['_global' => [$e->getMessage()]]);
        }
        return Response::redirect('/admin/testimonials/' . $id . '/edit');
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->route('id', 0);
        try {
            $this->testimonials->delete($id);
            $this->auth->writeAudit($this->currentUserId(), 'testimonial.delete', 'testimonial', $id, $request->ip());
            $this->session->flash('flash', 'Testimonial deleted.');
        } catch (\Throwable $e) {
            $this->session->flash('_errors', ['_global' => [$e->getMessage()]]);
        }
        return Response::redirect('/admin/testimonials');
    }

    private function renderForm(?array $row): Response
    {
        $translations = $row ? $this->testimonials->translationsFor((int) $row['id']) : [];
        return Response::html($this->view->render('admin/testimonials/form', [
            'page_title'   => 'Testimonial · Admin',
            'row'          => $row,
            'translations' => $translations,
            'old'          => flash('_old') ?? [],
            'errors'       => flash('_errors') ?? [],
            'locales'      => (array) config('locales.available', ['ar','fr','en']),
        ]));
    }

    private function validateAndFlash(array $post): bool
    {
        $v = new Validator($post, [
            'customer_name' => 'required|string|min:2|max:150',
            'customer_city' => 'nullable|string|max:120',
            'rating'        => 'required|int|min:1|max:5',
            'vehicle_purchased' => 'nullable|string|max:200',
            'sort_order'    => 'nullable|int',
            'body_en'       => 'required|string|min:5|max:2000',
        ]);
        if (! $v->passes()) {
            $this->session->flash('_old', $post);
            $this->session->flash('_errors', $v->errors());
            return false;
        }
        return true;
    }

    private function saveTranslations(int $id, array $post): void
    {
        foreach ((array) config('locales.available', ['ar','fr','en']) as $loc) {
            $body = trim((string) ($post['body_' . $loc] ?? ''));
            if ($body !== '') {
                $this->testimonials->upsertTranslation($id, $loc, $body);
            }
        }
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
