<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\Auth\AuthService;
use Closure;

final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthService $auth,
        private View        $view,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->auth->user();
        if ($user === null) {
            if (! str_starts_with($request->path, '/admin/login')) {
                return Response::redirect('/admin/login');
            }
        } else {
            $this->view->share('current_user', $user);
        }
        return $next($request);
    }
}
