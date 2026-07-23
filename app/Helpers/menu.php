<?php

if (!function_exists('isActiveMenu')) {

    function isActiveMenu(string $route): bool
    {
        $current = strtok($_SERVER['REQUEST_URI'], '?');
        return $current === $route;
    }
}