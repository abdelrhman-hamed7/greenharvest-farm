<?php
require_once 'includes/db.php';

unset($_SESSION['user_logged_in'], $_SESSION['user_name']);

header('Location: login.php');
exit;
