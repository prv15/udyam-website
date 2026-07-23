<?php

use App\Core\Session;

function flash(string $type, string $message): void
{
    Session::set('flash', [
        'type' => $type,
        'message' => $message
    ]);
}

function getFlash(): ?array
{
    $flash = Session::get('flash');

    Session::remove('flash');

    return $flash;
}