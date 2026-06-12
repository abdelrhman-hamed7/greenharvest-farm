<?php
require_once 'includes/db.php';

// Continue browsing as a guest while keeping the shopping cart session.
unset(
    $_SESSION['admin_logged_in'],
    $_SESSION['admin_username'],
    $_SESSION['user_logged_in'],
    $_SESSION['user_id'],
    $_SESSION['user_name']
);

header('Location: home.php');
exit;
