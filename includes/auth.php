<?php

require_once __DIR__ . '/functions.php';

function requireRole($allowedRoles)
{
    requireLogin();

    if (!in_array($_SESSION['role'], $allowedRoles)) {
        http_response_code(403);
        exit('Forbidden');
    }
}

function isUserLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function requireLogin()
{
    if (!isUserLoggedIn()) {
        redirect('login.php');
    }
}

function redirectIfLoggedIn()
{
    if (isUserLoggedIn()) {
        redirect('dashboard.php');
    }
}
