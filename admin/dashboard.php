<?php
require_once '../includes/admin-auth.php';
requireAdmin();

$totalProducts = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$activeProducts = (int) $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
$lowStockProducts = (int) $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active' AND stock <= 10")->fetchColumn();
$totalOrders = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalCustomers = (int) $pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
$totalRevenue = (float) $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn();

$recentOrdersStmt = $pdo->query(
    'SELECT orders.id, orders.order_number, orders.total_amount, orders.status, orders.created_at, customers.full_name
     FROM orders
     INNER JOIN customers ON orders.customer_id = customers.id
     ORDER BY orders.created_at DESC
     LIMIT 6'
);
$recentOrders = $recentOrdersStmt->fetchAll();

$bestSellingStmt = $pdo->query(
    'SELECT product_name, SUM(quantity) AS total_quantity, SUM(subtotal) AS total_sales
     FROM order_items
     GROUP BY product_name
     ORDER BY total_quantity DESC
     LIMIT 5'
);
$bestSellingProducts = $bestSellingStmt->fetchAll();

$pageTitle = 'Admin Dashboard';
require_once '../includes/header.php';
?>

<section class="admin-page-header">
    <div class="container">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3">
            <div>
                <span class="badge badge-soft mb-3">Admin area</span>
                <h1 class="display-6 fw-bold mb-2">Dashboard</h1>
                <p class="text-muted mb-0">Monitor products, orders, customers, and farm shop performance.</p>
            </div>
            <a href="products.php" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i> Manage Products
            </a>
        </div>
    </div>
</section>

<section class="admin-shell">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="dashboard-card h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="metric-label">Total Products</span>
                            <strong class="metric-value"><?php echo e($totalProducts); ?></strong>
                            <small><?php echo e($activeProducts); ?> active products</small>
                        </div>
                        <span class="metric-icon"><i class="bi bi-basket"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="dashboard-card h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="metric-label">Total Orders</span>
                            <strong class="metric-value"><?php echo e($totalOrders); ?></strong>
                            <small><?php echo e($lowStockProducts); ?> low-stock products</small>
                        </div>
                        <span class="metric-icon"><i class="bi bi-receipt"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="dashboard-card h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="metric-label">Total Revenue</span>
                            <strong class="metric-value"><?php echo formatPrice($totalRevenue); ?></strong>
                            <small>Excludes cancelled orders</small>
                        </div>
                        <span class="metric-icon"><i class="bi bi-cash-stack"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="dashboard-card h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="metric-label">Customers</span>
                            <strong class="metric-value"><?php echo e($totalCustomers); ?></strong>
                            <small>Created from checkout</small>
                        </div>
                        <span class="metric-icon"><i class="bi bi-people"></i></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="admin-panel">
                    <div class="admin-panel-header">
                        <div>
                            <h2 class="h4 fw-bold mb-1">Recent Orders</h2>
                            <p class="text-muted mb-0">Latest customer orders placed through checkout.</p>
                        </div>
                        <a href="orders.php" class="btn btn-outline-success btn-sm">View All</a>
                    </div>

                    <?php if (count($recentOrders) > 0): ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>
                                                <a class="fw-bold text-success" href="order-details.php?id=<?php echo e($order['id']); ?>">
                                                    <?php echo e($order['order_number']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo e($order['full_name']); ?></td>
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
                            <i class="bi bi-receipt"></i>
                            <h3 class="h5 fw-bold mt-3">No orders yet</h3>
                            <p class="text-muted mb-0">Orders will appear here after customers checkout.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="admin-panel h-100">
                    <div class="admin-panel-header">
                        <div>
                            <h2 class="h4 fw-bold mb-1">Best Selling Products</h2>
                            <p class="text-muted mb-0">Ranked by quantity sold.</p>
                        </div>
                    </div>

                    <?php if (count($bestSellingProducts) > 0): ?>
                        <div class="best-selling-list">
                            <?php foreach ($bestSellingProducts as $index => $product): ?>
                                <div class="best-selling-item">
                                    <span class="best-selling-rank"><?php echo e($index + 1); ?></span>
                                    <div>
                                        <strong><?php echo e($product['product_name']); ?></strong>
                                        <span><?php echo e($product['total_quantity']); ?> sold | <?php echo formatPrice($product['total_sales']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="admin-empty">
                            <i class="bi bi-graph-up"></i>
                            <h3 class="h5 fw-bold mt-3">No sales data yet</h3>
                            <p class="text-muted mb-0">Best sellers appear after orders are placed.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
