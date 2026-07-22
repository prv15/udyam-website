<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data);

        $viewFile = dirname(__DIR__) . "/Views/{$view}.php";

        if (!file_exists($viewFile)) {
            die("View '{$view}' not found.");
        }

        ob_start();

        require $viewFile;

        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layouts/main.php';
    }
}