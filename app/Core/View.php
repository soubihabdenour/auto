<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Lightweight PHP-template view engine.
 *
 * Usage in a view:
 *   <?php $this->extends('layouts/public'); ?>
 *   <?php $this->section('content'); ?>
 *       <h1>Hello</h1>
 *   <?php $this->endSection(); ?>
 *
 * Or simply render a template that does not call extends().
 */
final class View
{
    private string $viewPath;
    /** @var array<string, mixed> */
    private array $shared = [];
    /** @var array<string, string> */
    private array $sections = [];
    private ?string $currentSection = null;
    private ?string $extending = null;

    public function __construct(string $viewPath)
    {
        $this->viewPath = rtrim($viewPath, '/');
    }

    public function share(string $key, mixed $value): void
    {
        $this->shared[$key] = $value;
    }

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = []): string
    {
        $renderer = new self($this->viewPath);
        $renderer->shared = $this->shared;

        $output = $renderer->run($template, $data);

        if ($renderer->extending !== null) {
            $renderer->sections['content'] ??= $output;
            $output = $renderer->run($renderer->extending, $data);
        }

        return $output;
    }

    /** @param array<string, mixed> $data */
    private function run(string $template, array $data): string
    {
        $file = $this->resolve($template);

        $merged = array_merge($this->shared, $data);
        extract($merged, EXTR_SKIP);
        // $this is bound for $this->section(), $this->yield(), etc.

        ob_start();
        try {
            include $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }

    private function resolve(string $template): string
    {
        $file = $this->viewPath . '/' . ltrim($template, '/') . '.php';
        if (! is_file($file)) {
            throw new RuntimeException("View not found: {$template} (looked in {$file})");
        }
        return $file;
    }

    // ---------- Template API (called from within view files) ----------

    public function extends(string $layout): void
    {
        $this->extending = $layout;
    }

    public function section(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }

    public function endSection(): void
    {
        if ($this->currentSection === null) {
            throw new RuntimeException('endSection() called without matching section()');
        }
        $this->sections[$this->currentSection] = (string) ob_get_clean();
        $this->currentSection = null;
    }

    public function yield(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    /** @param array<string, mixed> $data */
    public function partial(string $template, array $data = []): string
    {
        return $this->run($template, $data);
    }
}
