<?php
require_once '../includes/admin-auth.php';
requireAdmin();

$allowedStatuses = ['pending', 'processing', 'completed', 'cancelled'];
$selectedStatus = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$message = $_SESSION['admin_message'] ?? null;
unset($_SESSION['admin_message']);

$sql = 'SELECT orders.id,
               orders.order_number,
               orders.total_amount,
               orders.status,
               orders.payment_method,
               orders.payment_status,
               orders.created_at,
               customers.full_name,
               customers.email,
               customers.phone,
               COUNT(order_items.id) AS item_count
        FROM orders
        INNER JOIN customers ON orders.customer_id = customers.id
        LEFT JOIN order_items ON orders.id = order_items.order_id
        WHERE 1 = 1';

$params = [];

if (in_array($selectedStatus, $allowedStatuses, true)) {
    $sql .= ' AND orders.status = :status';
    $params['status'] = $selectedStatus;
}

if ($search !== '') {
    $sql .= ' AND (orders.order_number LIKE :search_order OR customers.full_name LIKE :search_customer OR customers.email LIKE :search_email)';
    $params['search_order'] = '%' . $search . '%';
    $params['search_customer'] = '%' . $search . '%';
    $params['search_email'] = '%' . $search . '%';
}

$sql .= ' GROUP BY orders.id
          ORDER BY orders.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$pageTitle = 'Manage Orders';
require_once '../includes/header.php';
?>

<section class="admin-page-header">
    <div class="container">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3">
            <div>
                <span class="badge badge-soft mb-3">Order management</span>
                <h1 class="display-6 fw-bold mb-2">Orders</h1>
                <p class="text-muted mb-0">View customer orders, search by customer, and filter by order status.</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-success">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
            </a>
        </div>
    </div>
</section>

<section class="admin-shell">
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo e($message['type']); ?> alert-dismissible fade show" role="alert" data-auto-dismiss="true">
                <?php echo e($message['text']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="filter-panel mb-4">
            <form action="orders.php" method="get" class="row g-3 align-items-end">
                <div class="col-lg-6">
                    <label for="search" class="form-label fw-bold">Search orders</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input
                            type="text"
                            class="form-control"
                            id="search"
                            name="search"
                            value="<?php echo e($search); ?>"
                            placeholder="Search order number, customer, or email">
                    </div>
                </div>
                <div class="col-lg-3">
                    <label for="status" class="form-label fw-bold">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All statuses</option>
                        <?php foreach ($allowedStatuses as $status): ?>
                            <option value="<?php echo e($status); ?>" <?php echo $selectedStatus === $status ? 'selected' : ''; ?>>
                                <?php echo e(ucfirst($status)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-3 d-grid d-sm-flex gap-2">
                    <button type="submit" class="btn btn-success flex-fill">
                        <i class="bi bi-funnel me-1"></i> Apply
                    </button>
                    <a href="orders.php" class="btn btn-outline-success flex-fill">Reset</a>
                </div>
            </form>
        </div>

        <div class="admin-panel">
            <div class="admin-panel-header">
                <div>
                    <h2 class="h4 fw-bold mb-1">All Orders</h2>
                    <p class="text-muted mb-0">Showing <?php echo e(count($orders)); ?> order<?php echo count($orders) === 1 ? '' : 's'; ?>.</p>
                </div>
            </div>

            <?php if (count($orders) > 0): ?>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($order['order_number']); ?></strong>
                                        <span class="d-block text-muted small">
                                            <?php echo e($order['payment_method']); ?> |
                                            <?php echo e(ucfirst($order['payment_status'] ?? 'pending')); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo e($order['full_name']); ?>
                                        <span class="d-block text-muted small"><?php echo e($order['email']); ?></span>
                                    </td>
                                    <td><?php echo e($order['item_count']); ?></td>
                                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                                    <td>
                                        <span class="badge admin-status admin-status-<?php echo e($order['status']); ?>">
                                            <?php echo e(ucfirst($order['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo e(date('M d, Y', strtotime($order['created_at']))); ?></td>
                                    <td class="text-end">
                                        <a href="order-details.php?id=<?php echo e($order['id']); ?>" class="btn btn-outline-success btn-sm">
                                            <i class="bi bi-eye me-1"></i> Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="admin-empty">
                    <i class="bi bi-receipt"></i>
                    <h3 class="h5 fw-bold mt-3">No orders found</h3>
                    <p class="text-muted mb-0">Try another filter or wait until customers place orders.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
