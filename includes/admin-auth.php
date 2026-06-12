<?php
require_once __DIR__ . '/db.php';

function isAdminLoggedIn()
{
    return !empty($_SESSION['admin_logged_in']);
}

function requireAdmin()
{
    if (!isAdminLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
}

function getAdminUsername()
{
    return getenv('ADMIN_USER') ?: 'admin';
}

function getAdminPassword()
{
    return getenv('ADMIN_PASS') ?: 'admin3017';
}
