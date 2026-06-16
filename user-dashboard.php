<?php
require_once 'includes/db.php';

if (empty($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit;
}

$account = null;
$currentOrder = null;
$orderHistory = [];
$activeOrderCount = 0;
if (!empty($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM customer_accounts WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $account = $stmt->fetch();
}

$cartCount = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}

if ($account) {
    $orderSelect = 'SELECT orders.id,
                           orders.order_number,
                           orders.total_amount,
                           orders.status,
                           orders.payment_method,
                           orders.payment_status,
                           orders.created_at,
                           COUNT(order_items.id) AS item_count
                    FROM orders
                    INNER JOIN customers ON orders.customer_id = customers.id
                    LEFT JOIN order_items ON orders.id = order_items.order_id
                    WHERE LOWER(customers.email) = LOWER(:email)';

    $orderGroup = ' GROUP BY orders.id,
                            orders.order_number,
                            orders.total_amount,
                            orders.status,
                            orders.payment_method,
                            orders.payment_status,
                            orders.created_at';

    $currentStmt = $pdo->prepare(
        $orderSelect .
        " AND orders.status IN ('pending', 'processing')" .
        $orderGroup .
        ' ORDER BY orders.created_at DESC
          LIMIT 1'
    );
    $currentStmt->execute(['email' => $account['email']]);
    $currentOrder = $currentStmt->fetch();

    $activeCountStmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM orders
         INNER JOIN customers ON orders.customer_id = customers.id
         WHERE LOWER(customers.email) = LOWER(:email)
           AND orders.status IN ('pending', 'processing')"
    );
    $activeCountStmt->execute(['email' => $account['email']]);
    $activeOrderCount = (int) $activeCountStmt->fetchColumn();

    $historyStmt = $pdo->prepare(
        $orderSelect .
        $orderGroup .
        ' ORDER BY orders.created_at DESC
          LIMIT 8'
    );
    $historyStmt->execute(['email' => $account['email']]);
    $orderHistory = $historyStmt->fetchAll();
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
                    <span class="metric-icon mb-3"><i class="bi bi-receipt"></i></span>
                    <span class="metric-label">Current Orders</span>
                    <strong class="metric-value"><?php echo e($activeOrderCount); ?></strong>
                    <small>Pending or processing orders connected to your account.</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card h-100">
                    <span class="metric-icon mb-3"><i class="bi bi-clock-history"></i></span>
                    <span class="metric-label">Order History</span>
                    <strong class="metric-value"><?php echo e(count($orderHistory)); ?></strong>
                    <small>Recent farm orders placed using your account email.</small>
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
            <div class="row g-4 mt-1">
                <div class="col-lg-5">
                    <div class="admin-panel h-100">
                        <div class="admin-panel-header">
                            <div>
                                <h2 class="h4 fw-bold mb-1">Current Order</h2>
                                <p class="text-muted mb-0">Your latest pending or processing order.</p>
                            </div>
                        </div>

                        <?php if ($currentOrder): ?>
                            <div class="p-4">
                                <div class="d-flex justify-content-between gap-3 mb-3">
                                    <div>
                                        <span class="text-muted small d-block">Order Number</span>
                                        <strong><?php echo e($currentOrder['order_number']); ?></strong>
                                    </div>
                                    <span class="badge admin-status admin-status-<?php echo e($currentOrder['status']); ?>">
                                        <?php echo e(ucfirst($currentOrder['status'])); ?>
                                    </span>
                                </div>

                                <div class="profile-grid">
                                    <div><span>Total</span><strong><?php echo formatPrice($currentOrder['total_amount']); ?></strong></div>
                                    <div><span>Items</span><strong><?php echo e($currentOrder['item_count']); ?></strong></div>
                                    <div><span>Payment</span><strong><?php echo e($currentOrder['payment_method']); ?></strong></div>
                                    <div><span>Placed</span><strong><?php echo e(date('M d, Y', strtotime($currentOrder['created_at']))); ?></strong></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="admin-empty">
                                <i class="bi bi-receipt"></i>
                                <h3 class="h5 fw-bold mt-3">No active order</h3>
                                <p class="text-muted mb-4">Your next pending order will appear here after checkout.</p>
                                <a href="products.php" class="btn btn-success">Shop Products</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="admin-panel h-100">
                        <div class="admin-panel-header">
                            <div>
                                <h2 class="h4 fw-bold mb-1">Order History</h2>
                                <p class="text-muted mb-0">Recent orders placed with <?php echo e($account['email']); ?>.</p>
                            </div>
                        </div>

                        <?php if (count($orderHistory) > 0): ?>
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Order</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orderHistory as $order): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo e($order['order_number']); ?></strong>
                                                    <span class="d-block text-muted small"><?php echo e($order['payment_method']); ?></span>
                                                </td>
                                                <td><?php echo e($order['item_count']); ?></td>
                                                <td><?php echo formatPrice($order['total_amount']); ?></td>
                                                <td>
                                                    <span class="badge admin-status admin-status-<?php echo e($order['status']); ?>">
                                                        <?php echo e(ucfirst($order['status'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo e(date('M d, Y', strtotime($order['created_at']))); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="admin-empty">
                                <i class="bi bi-clock-history"></i>
                                <h3 class="h5 fw-bold mt-3">No order history yet</h3>
                                <p class="text-muted mb-0">Orders will appear here after you complete checkout.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

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
