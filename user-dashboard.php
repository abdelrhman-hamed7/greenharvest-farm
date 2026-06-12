<?php
require_once 'includes/db.php';

if (empty($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit;
}

$account = null;
if (!empty($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM customer_accounts WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $account = $stmt->fetch();
}

$cartCount = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}

$pageTitle = 'User Dashboard';
require_once 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <span class="hero-kicker"><i class="bi bi-person-check-fill"></i> Customer dashboard</span>
        <h1 class="display-5 fw-bold mb-3">Welcome, <?php echo e($_SESSION['user_name']); ?></h1>
        <p class="lead mb-0">Continue shopping, review your cart, or complete checkout.</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="dashboard-card h-100">
                    <span class="metric-icon mb-3"><i class="bi bi-bag"></i></span>
                    <span class="metric-label">Cart Items</span>
                    <strong class="metric-value"><?php echo e($cartCount); ?></strong>
                    <small>Products currently in your shopping cart.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card h-100">
                    <span class="metric-icon mb-3"><i class="bi bi-basket2"></i></span>
                    <span class="metric-label">Shop Products</span>
                    <strong class="metric-value">Fresh</strong>
                    <small>Browse vegetables, fruits, dairy, coffee, tea, and farm products.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card h-100">
                    <span class="metric-icon mb-3"><i class="bi bi-truck"></i></span>
                    <span class="metric-label">Delivery</span>
                    <strong class="metric-value">COD</strong>
                    <small>Cash on delivery is available for farm orders.</small>
                </div>
            </div>
        </div>

        <div class="user-dashboard-actions">
            <a href="products.php" class="btn btn-success">
                <i class="bi bi-basket me-1"></i> Continue Shopping
            </a>
            <a href="cart.php" class="btn btn-outline-success">
                <i class="bi bi-bag me-1"></i> View Cart
            </a>
            <a href="checkout.php" class="btn btn-outline-success">
                <i class="bi bi-credit-card me-1"></i> Checkout
            </a>
        </div>

        <?php if ($account): ?>
            <div class="admin-panel mt-4">
                <div class="admin-panel-header">
                    <div>
                        <h2 class="h4 fw-bold mb-1">Your Customer Information</h2>
                        <p class="text-muted mb-0">This information is stored in the customer account database.</p>
                    </div>
                </div>
                <div class="profile-grid">
                    <div><span>Full Name</span><strong><?php echo e($account['full_name']); ?></strong></div>
                    <div><span>Last Name</span><strong><?php echo e($account['last_name'] ?? ''); ?></strong></div>
                    <div><span>Email</span><strong><?php echo e($account['email']); ?></strong></div>
                    <div><span>Phone</span><strong><?php echo e($account['phone']); ?></strong></div>
                    <div><span>City</span><strong><?php echo e($account['city']); ?></strong></div>
                    <div><span>Address</span><strong><?php echo e($account['address']); ?></strong></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
