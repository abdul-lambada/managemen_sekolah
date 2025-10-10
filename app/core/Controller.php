<?php

declare(strict_types=1);

abstract class Controller
{
    protected array $data = [];

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Helper for returning standard payload.
     */
    protected function view(string $view, array $data = [], string $title = ''): array
    {
        return [
            'view' => $view,
            'data' => $data,
            'title' => $title,
        ];
    }

    protected function requireRole(string ...$roles): void
    {
        if (!has_role(...$roles)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }
}
