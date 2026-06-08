<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Exception\NotFoundException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Repositories\LeadRepository;
use App\Services\Auth\AuthService;

final class LeadController
{
    private const STATUSES = ['new', 'contacted', 'qualified', 'negotiating', 'won', 'lost'];
    private const TYPES    = ['inquiry', 'quotation', 'reservation', 'whatsapp'];
    private const SOURCES  = ['vehicle_page', 'listing', 'homepage', 'contact', 'direct'];

    public function __construct(
        private View           $view,
        private LeadRepository $leads,
        private AuthService    $auth,
        private Session        $session,
    ) {}

    public function index(Request $request): Response
    {
        $filters = [
            'status'    => (string) $request->input('status', ''),
            'lead_type' => (string) $request->input('lead_type', ''),
            'source'    => (string) $request->input('source', ''),
            'q'         => (string) $request->input('q', ''),
        ];
        $page    = max(1, (int) $request->input('page', 1));
        $perPage = 25;
        $offset  = ($page - 1) * $perPage;

        $rows  = $this->safe(fn () => $this->leads->adminList($filters, $perPage, $offset));
        $total = $this->safe(fn () => $this->leads->adminCount($filters), 0);

        return Response::html($this->view->render('admin/leads/index', [
            'page_title' => 'Leads · Admin',
            'rows'       => $rows,
            'total'      => $total,
            'page'       => $page,
            'pages'      => max(1, (int) ceil($total / $perPage)),
            'filters'    => $filters,
            'statuses'   => self::STATUSES,
            'types'      => self::TYPES,
            'sources'    => self::SOURCES,
        ]));
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->route('id', 0);
        $lead  = $this->leads->find($id)
            ?? throw new NotFoundException("Lead {$id} not found");
        $notes = $this->safe(fn () => $this->leads->notesFor($id));
        return Response::html($this->view->render('admin/leads/show', [
            'page_title' => 'Lead #' . $id . ' · Admin',
            'lead'       => $lead,
            'notes'      => $notes,
            'statuses'   => self::STATUSES,
        ]));
    }

    public function updateStatus(Request $request): Response
    {
        $id = (int) $request->route('id', 0);
        $status = (string) $request->input('status', '');
        if (! in_array($status, self::STATUSES, true)) {
            $this->session->flash('_errors', ['_global' => ['Invalid status']]);
            return Response::redirect('/admin/leads/' . $id);
        }
        try {
            $this->leads->updateStatus($id, $status);
            $this->auth->writeAudit(
                $this->currentUserId(), 'lead.status', 'lead', $id, $request->ip(),
                ['status' => $status]
            );
            $this->session->flash('flash', 'Status updated.');
        } catch (\Throwable $e) {
            $this->session->flash('_errors', ['_global' => [$e->getMessage()]]);
        }
        return Response::redirect('/admin/leads/' . $id);
    }

    public function addNote(Request $request): Response
    {
        $id = (int) $request->route('id', 0);
        $v = new Validator($request->post, ['body' => 'required|string|min:2|max:5000']);
        if (! $v->passes()) {
            $this->session->flash('_errors', $v->errors());
            return Response::redirect('/admin/leads/' . $id);
        }
        try {
            $this->leads->addNote($id, $this->currentUserId(), (string) $v->validated()['body']);
            $this->session->flash('flash', 'Note added.');
        } catch (\Throwable $e) {
            $this->session->flash('_errors', ['_global' => [$e->getMessage()]]);
        }
        return Response::redirect('/admin/leads/' . $id);
    }

    public function exportCsv(Request $request): Response
    {
        $filters = [
            'status'    => (string) $request->input('status', ''),
            'lead_type' => (string) $request->input('lead_type', ''),
            'source'    => (string) $request->input('source', ''),
            'q'         => (string) $request->input('q', ''),
        ];
        $rows = $this->safe(fn () => $this->leads->export($filters));

        // Build CSV in memory
        $fh = fopen('php://temp', 'r+');
        $headers = [
            'id', 'created_at', 'lead_type', 'status', 'source', 'locale',
            'name', 'phone', 'whatsapp', 'email', 'city', 'country',
            'vehicle_id', 'vehicle', 'message',
            'utm_source', 'utm_medium', 'utm_campaign', 'referrer',
        ];
        fputcsv($fh, $headers, ',', '"', '\\');
        foreach ($rows as $r) {
            fputcsv($fh, [
                $r['id'], $r['created_at'], $r['lead_type'], $r['status'], $r['source'], $r['locale'],
                $r['name'], $r['phone'], $r['whatsapp'], $r['email'], $r['city'], $r['country'],
                $r['vehicle_id'], $r['vehicle_label'] ?? '', $r['message'],
                $r['utm_source'], $r['utm_medium'], $r['utm_campaign'], $r['referrer'],
            ], ',', '"', '\\');
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return (new Response())
            ->status(200)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="leads-' . date('Y-m-d') . '.csv"')
            ->body((string) $csv);
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
