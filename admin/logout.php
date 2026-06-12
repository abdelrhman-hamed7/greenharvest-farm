<?php
require_once '../includes/admin-auth.php';

unset($_SESSION['admin_logged_in'], $_SESSION['admin_username']);

header('Location: ../login.php');
exit;
