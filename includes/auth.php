<?php

require_once __DIR__ . '/functions.php';

function requireRole($allowedRoles)
{
    requireLogin();

    if (!in_array($_SESSION['user_role'], $allowedRoles)) {
        redirect("login.php");
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
        redirect('index.php');
    }
}