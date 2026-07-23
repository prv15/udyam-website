<?php

namespace App\Core\Support;

use App\Core\Session;

class Flash
{
    public static function success(string $message): void
    {
        Session::set('flash', [
            'type' => 'success',
            'message' => $message
        ]);
    }

    public static function error(string $message): void
    {
        Session::set('flash', [
            'type' => 'error',
            'message' => $message
        ]);
    }

    public static function get(): ?array
    {
        $flash = Session::get('flash');

        Session::remove('flash');

        return $flash;
    }
}