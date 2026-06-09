<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Exception\NotFoundException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Repositories\ReservationRepository;
use App\Services\Auth\AuthService;
use App\Services\Reservation\ReservationMailer;
use App\Services\Reservation\ReservationService;

final class ReservationController
{
    private const STATUSES = ['pending_deposit', 'confirmed', 'expired', 'cancelled', 'converted'];

    public function __construct(
        private View                  $view,
        private ReservationRepository $reservations,
        private ReservationService    $service,
        private ReservationMailer     $mailer,
        private AuthService           $auth,
        private Session               $session,
    ) {}

    public function index(Request $request): Response
    {
        $filters = [
            'status'     => (string) $request->input('status', ''),
            'q'          => (string) $request->input('q', ''),
            'vehicle_id' => (int)    $request->input('vehicle_id', 0),
        ];
        $page    = max(1, (int) $request->input('page', 1));
        $perPage = 25;
        $offset  = ($page - 1) * $perPage;

        $rows  = $this->safe(fn () => $this->reservations->adminList($filters, $perPage, $offset));
        $total = $this->safe(fn () => $this->reservations->adminCount($filters), 0);

        return Response::html($this->view->render('admin/reservations/index', [
            'page_title' => 'Reservations · Admin',
            'rows'       => $rows,
            'total'      => $total,
            'page'       => $page,
            'pages'      => max(1, (int) ceil($total / $perPage)),
            'filters'    => $filters,
            'statuses'   => self::STATUSES,
        ]));
    }

    public function show(Request $request): Response
    {
        $id  = (int) $request->route('id', 0);
        $row = $this->reservations->findById($id)
            ?? throw new NotFoundException("Reservation {$id} not found");

        return Response::html($this->view->render('admin/reservations/show', [
            'page_title'  => 'Reservation ' . $row['reference'] . ' · Admin',
            'reservation' => $row,
            'public_url'  => url('/' . current_locale() . '/reservations/' . $row['reference']),
        ]));
    }

    public function confirm(Request $request): Response
    {
        $id   = (int) $request->route('id', 0);
        $note = trim((string) $request->input('admin_note', '')) ?: null;
        try {
            $this->service->confirm($id, (int) $this->currentUserId(), $note);
            $this->auth->writeAudit(
                $this->currentUserId(), 'reservation.confirm', 'reservation', $id, $request->ip()
            );
            $row = $this->reservations->findById($id);
            if ($row !== null) {
                try {
                    $this->mailer->confirmed($row, url('/' . current_locale() . '/reservations/' . $row['reference']));
                } catch (\Throwable) {}
            }
            $this->session->flash('flash', 'Reservation confirmed — vehicle is now reserved.');
        } catch (\Throwable $e) {
            $this->session->flash('_errors', ['_global' => [$e->getMessage()]]);
        }
        return Response::redirect('/admin/reservations/' . $id);
    }

    public function cancel(Request $request): Response
    {
        $id     = (int) $request->route('id', 0);
        $reason = trim((string) $request->input('cancellation_reason', '')) ?: null;
        try {
            $this->service->cancel($id, (int) $this->currentUserId(), $reason);
            $this->auth->writeAudit(
                $this->currentUserId(), 'reservation.cancel', 'reservation', $id, $request->ip(),
                $reason ? ['reason' => $reason] : []
            );
            $row = $this->reservations->findById($id);
            if ($row !== null) {
                try {
                    $this->mailer->cancelled($row, url('/' . current_locale() . '/reservations/' . $row['reference']));
                } catch (\Throwable) {}
            }
            $this->session->flash('flash', 'Reservation cancelled — vehicle is available again.');
        } catch (\Throwable $e) {
            $this->session->flash('_errors', ['_global' => [$e->getMessage()]]);
        }
        return Response::redirect('/admin/reservations/' . $id);
    }

    public function convert(Request $request): Response
    {
        $id = (int) $request->route('id', 0);
        try {
            $this->service->convertToSale($id);
            $this->auth->writeAudit(
                $this->currentUserId(), 'reservation.convert', 'reservation', $id, $request->ip()
            );
            $this->session->flash('flash', 'Reservation converted — vehicle marked sold.');
        } catch (\Throwable $e) {
            $this->session->flash('_errors', ['_global' => [$e->getMessage()]]);
        }
        return Response::redirect('/admin/reservations/' . $id);
    }

    public function addNote(Request $request): Response
    {
        $id = (int) $request->route('id', 0);
        $v  = new Validator($request->post, ['admin_note' => 'required|string|min:1|max:5000']);
        if (! $v->passes()) {
            $this->session->flash('_errors', $v->errors());
            return Response::redirect('/admin/reservations/' . $id);
        }
        try {
            $this->reservations->addAdminNote($id, (string) $v->validated()['admin_note']);
            $this->session->flash('flash', 'Note saved.');
        } catch (\Throwable $e) {
            $this->session->flash('_errors', ['_global' => [$e->getMessage()]]);
        }
        return Response::redirect('/admin/reservations/' . $id);
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
