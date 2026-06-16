<?php
require_once __DIR__ . '/db.php';

$pageTitle = $pageTitle ?? 'GreenHarvest Farm';
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$isAdminPage = strpos($scriptPath, '/admin/') !== false;
$basePath = $isAdminPage ? '../' : '';
$adminLoggedIn = !empty($_SESSION['admin_logged_in']);
$userLoggedIn = !empty($_SESSION['user_logged_in']);
$userDisplayName = $userLoggedIn ? trim($_SESSION['user_name'] ?? 'Customer') : '';
if ($userDisplayName === '') {
    $userDisplayName = 'Customer';
}
$brandHref = $isAdminPage ? ($adminLoggedIn ? 'dashboard.php' : '../login.php') : 'home.php';

$cartCount = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}

$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> | GreenHarvest Farm</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo $basePath; ?>assets/brand/favicon.svg?v=20260612b">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $basePath; ?>css/style.css?v=20260612a" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand brand-mark" href="<?php echo e($brandHref); ?>">
                <span class="brand-icon"><i class="bi bi-flower1"></i></span>
                <span>
                    <strong>GreenHarvest</strong>
                    <small>Farm</small>
                </span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <?php if ($isAdminPage): ?>
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                        <?php if ($adminLoggedIn): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                                    <i class="bi bi-speedometer2 me-1"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage === 'products.php' ? 'active' : ''; ?>" href="products.php">
                                    <i class="bi bi-basket me-1"></i> Products
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $currentPage === 'orders.php' ? 'active' : ''; ?>" href="orders.php">
                                    <i class="bi bi-receipt me-1"></i> Orders
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="btn btn-outline-success btn-sm ms-lg-2" href="<?php echo $basePath; ?>home.php">
                                <i class="bi bi-shop me-1"></i> View Store
                            </a>
                        </li>
                        <?php if ($adminLoggedIn): ?>
                            <li class="nav-item">
                                <a class="btn btn-success btn-sm ms-lg-2" href="logout.php">
                                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                <?php else: ?>
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'home.php' ? 'active' : ''; ?>" href="home.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'products.php' ? 'active' : ''; ?>" href="products.php">Shop</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage === 'about.php' ? 'active' : ''; ?>" href="about.php">About</a>
                        </li>
                        <?php if ($adminLoggedIn): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/logout.php">Logout</a>
                            </li>
                        <?php elseif ($userLoggedIn): ?>
                            <li class="nav-item">
                                <a class="customer-nav-badge <?php echo $currentPage === 'user-dashboard.php' ? 'active' : ''; ?>" href="user-dashboard.php" aria-label="Open customer dashboard">
                                    <i class="bi bi-person-circle me-1"></i>
                                    <?php echo e($userDisplayName); ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-outline-success login-nav-button ms-lg-2" href="logout.php">
                                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="btn btn-outline-success login-nav-button ms-lg-2 <?php echo $currentPage === 'login.php' ? 'active' : ''; ?>" href="login.php">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="btn btn-success cart-button ms-lg-2" href="cart.php">
                                <i class="bi bi-bag me-1"></i>
                                Cart
                                <span class="badge text-bg-light ms-1 js-cart-count"><?php echo e($cartCount); ?></span>
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php if ($flashMessage): ?>
        <div class="toast-container app-toast-container">
            <div class="toast align-items-center text-bg-<?php echo e($flashMessage['type']); ?> border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2800">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-check-circle me-2"></i><?php echo e($flashMessage['message']); ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <main>
