<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Services\Auth\AuthService;

final class AuthController
{
    public function __construct(
        private View        $view,
        private AuthService $auth,
        private Session     $session,
    ) {}

    public function showLogin(Request $request): Response
    {
        if ($this->auth->check()) {
            return Response::redirect('/admin');
        }
        $html = $this->view->render('admin/auth/login', [
            'page_title' => 'Admin · Sign in',
            'errors'     => $this->session->getFlash('_errors', []),
            'old'        => $this->session->getFlash('_old', []),
            'flash'      => $this->session->getFlash('flash'),
        ]);
        return Response::html($html);
    }

    public function login(Request $request): Response
    {
        $validator = new Validator(
            data: $request->post,
            rules: [
                'email'    => 'required|email|max:190',
                'password' => 'required|string|min:1|max:255',
            ],
        );
        if (! $validator->passes()) {
            $this->session->flash('_old',    ['email' => (string) $request->input('email', '')]);
            $this->session->flash('_errors', $validator->errors());
            return Response::redirect('/admin/login');
        }

        $email    = (string) $validator->validated()['email'];
        $password = (string) $validator->validated()['password'];

        $ok = $this->auth->attempt($email, $password, $request->ip());

        if (! $ok) {
            $this->session->flash('_old',    ['email' => $email]);
            $this->session->flash('_errors', ['_global' => ['Invalid credentials or account locked.']]);
            return Response::redirect('/admin/login');
        }

        return Response::redirect('/admin');
    }

    public function logout(Request $request): Response
    {
        $this->auth->logout($request->ip());
        return Response::redirect('/admin/login');
    }
}
