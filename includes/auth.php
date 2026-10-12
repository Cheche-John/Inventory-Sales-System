<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit();
    }
}

function requireRole(array $roles)
{
    requireLogin();

    $role = strtolower($_SESSION['role'] ?? '');

    if (!in_array($role, $roles, true)) {
        http_response_code(403);
        exit('Access denied. You do not have permission to view this page.');
    }
}