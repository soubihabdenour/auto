<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Exception\HttpException;
use App\Core\Exception\NotFoundException;
use Throwable;

final class Application
{
    public function __construct(
        private Container $container,
        private Router $router,
        private Config $config,
    ) {}

    public function run(): void
    {
        $request = Request::capture();
        $this->container->instance(Request::class, $request);

        try {
            $response = $this->router->dispatch($request);
        } catch (NotFoundException $e) {
            $response = $this->renderError(404, $e);
        } catch (HttpException $e) {
            $response = $this->renderError($e->statusCode, $e);
        } catch (Throwable $e) {
            $response = $this->renderError(500, $e);
        }

        $response->send();
    }

    private function renderError(int $status, Throwable $e): Response
    {
        // In dev mode, surface the underlying error
        if ($this->config->get('app.debug') && $status >= 500) {
            $body  = '<pre style="font: 14px monospace; padding: 16px; background:#fff5f5; color:#7a0c0c; white-space:pre-wrap;">';
            $body .= htmlspecialchars(get_class($e) . ': ' . $e->getMessage(), ENT_QUOTES);
            $body .= "\n\n" . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES);
            $body .= '</pre>';
            return Response::html($body, $status);
        }

        // Use a basic templated error page; full layout requires view binding
        try {
            /** @var View $view */
            $view = $this->container->get(View::class);
            $template = match (true) {
                $status === 404 => 'errors/404',
                default         => 'errors/500',
            };
            $html = $view->render($template, ['status' => $status]);
            return Response::html($html, $status);
        } catch (Throwable) {
            return Response::text("Error {$status}", $status);
        }
    }
}
