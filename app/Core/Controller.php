<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    protected function adminView(string $view, array $data = []): void
    {
        extract($data);

        $content = dirname(__DIR__) . '/Views/admin/' . $view . '.php';

        if (!file_exists($content)) {
            throw new \RuntimeException("View '{$view}' not found.");
        }

        require dirname(__DIR__) . '/Views/admin/layouts/master.php';
    }

    /**
     * Redirect to URL.
     */
    protected function redirect(string $url): never
    {
        header('Location: ' . url($url));
        exit;
    }

    /**
     * Redirect back.
     */
    protected function back(): never
    {
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    /**
     * JSON Response.
     */
    protected function json(array $data, int $status = 200): never
    {
        http_response_code($status);

        header('Content-Type: application/json');

        echo json_encode($data);

        exit;
    }

    /**
     * Success Flash Message.
     */
    protected function success(string $message): void
    {
        Session::flash('success', $message);
    }

    /**
     * Error Flash Message.
     */
    protected function error(string $message): void
    {
        Session::flash('error', $message);
    }

    protected function info(string $message): void
    {
        Session::flash('info', $message);
    }

    /**
     * 404 Response.
     */
    protected function abort404(): never
    {
        http_response_code(404);
        exit('404 - Page Not Found');
    }

    /**
     * 403 Response.
     */
    protected function abort403(): never
    {
        http_response_code(403);
        exit('403 - Forbidden');
    }
    /**
 * Redirect with success message.
 */
protected function redirectSuccess(
    string $url,
    string $message
): never {

    $this->success($message);

    $this->redirect($url);
}

/**
 * Redirect with error message.
 */
protected function redirectError(
    string $url,
    string $message
): never {

    $this->error($message);

    $this->redirect($url);
}

/**
 * Redirect with a neutral informational message (not an error).
 */
protected function redirectInfo(
    string $url,
    string $message
): never {

    $this->info($message);

    $this->redirect($url);
}
}
