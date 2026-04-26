<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirect($location)
{
    header('Location: ' . $location);
    exit;
}

function setFlashData($key, $value)
{
    $_SESSION['flash'][$key] = $value;
}

function getFlashData($key, $default = null)
{
    if (!isset($_SESSION['flash'][$key])) {
        return $default;
    }

    $value = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $value;
}

function escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
