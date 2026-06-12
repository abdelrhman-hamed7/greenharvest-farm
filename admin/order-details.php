<?php
require_once '../includes/admin-auth.php';
requireAdmin();

$allowedStatuses = ['pending', 'processing', 'completed', 'cancelled'];
$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$orderId) {
    header('Location: orders.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] ?? '';

    if (in_array($newStatus, $allowedStatuses, true)) {
        $updateStmt = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');
        $updateStmt->execute([
            'status' => $newStatus,
            'id' => $orderId,
        ]);

        $_SESSION['admin_message'] = [
            'type' => 'success',
            'text' => 'Order status updated successfully.',
        ];
    } else {
        $_SESSION['admin_message'] = [
            'type' => 'danger',
            'text' => 'Invalid order status selected.',
        ];
    }

    header('Location: order-details.php?id=' . $orderId);
    exit;
}

$stmt = $pdo->prepare(
    'SELECT orders.*,
            customers.full_name,
            customers.email,
            customers.phone,
            customers.address,
            customers.city,
            customers.notes
     FROM orders
     INNER JOIN customers ON orders.customer_id = customers.id
     WHERE orders.id = :id
     LIMIT 1'
);
$stmt->execute(['id' => $orderId]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['admin_message'] = [
        'type' => 'danger',
        'text' => 'Order not found.',
    ];
    header('Location: orders.php');
    exit;
}

$itemsStmt = $pdo->prepare(
    'SELECT order_items.*, products.image_path
     FROM order_items
     LEFT JOIN products ON order_items.product_id = products.id
     WHERE order_items.order_id = :order_id
     ORDER BY order_items.id ASC'
);
$itemsStmt->execute(['order_id' => $orderId]);
$orderItems = $itemsStmt->fetchAll();

$message = $_SESSION['admin_message'] ?? null;
unset($_SESSION['admin_message']);

$pageTitle = 'Order Details';
require_once '../includes/header.php';
?>

<section class="admin-page-header">
    <div class="container">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3">
            <div>
                <span class="badge badge-soft mb-3">Order details</span>
                <h1 class="display-6 fw-bold mb-2"><?php echo e($order['order_number']); ?></h1>
                <p class="text-muted mb-0">Review customer information, ordered products, and current status.</p>
            </div>
            <a href="orders.php" class="btn btn-outline-success">
                <i class="bi bi-arrow-left me-1"></i> Back To Orders
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

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="admin-panel">
                    <div class="admin-panel-header">
                        <div>
                            <h2 class="h4 fw-bold mb-1">Order Items</h2>
                            <p class="text-muted mb-0"><?php echo e(count($orderItems)); ?> product<?php echo count($orderItems) === 1 ? '' : 's'; ?> in this order.</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="admin-product-cell">
                                                <div class="admin-product-thumb">
                                                    <?php if (!empty($item['image_path'])): ?>
                                                        <img src="../<?php echo e($item['image_path']); ?>" alt="<?php echo e($item['product_name']); ?>">
                                                    <?php else: ?>
                                                        <i class="bi bi-image"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <strong><?php echo e($item['product_name']); ?></strong>
                                                    <span>Product ID: <?php echo e($item['product_id'] ?: 'Deleted product'); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo e($item['quantity']); ?></td>
                                        <td><?php echo formatPrice($item['price']); ?></td>
                                        <td class="text-end fw-bold"><?php echo formatPrice($item['subtotal']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="admin-order-sidebar">
                    <div class="order-info-card">
                        <h2 class="h5 fw-bold mb-3">Order Summary</h2>
                        <div class="summary-row">
                            <span>Status</span>
                            <strong><?php echo e(ucfirst($order['status'])); ?></strong>
                        </div>
                        <div class="summary-row">
                            <span>Payment</span>
                            <strong><?php echo e($order['payment_method']); ?></strong>
                        </div>
                        <?php if (!empty($order['payment_phone'])): ?>
                            <div class="summary-row">
                                <span>Payment Phone</span>
                                <strong><?php echo e($order['payment_phone']); ?></strong>
                            </div>
                        <?php endif; ?>
                        <div class="summary-row">
                            <span>Payment Status</span>
                            <strong><?php echo e(ucfirst($order['payment_status'] ?? 'pending')); ?></strong>
                        </div>
                        <div class="summary-row">
                            <span>Date</span>
                            <strong><?php echo e(date('M d, Y', strtotime($order['created_at']))); ?></strong>
                        </div>
                        <div class="summary-row summary-total">
                            <span>Total</span>
                            <strong><?php echo formatPrice($order['total_amount']); ?></strong>
                        </div>

                        <form action="order-details.php?id=<?php echo e($orderId); ?>" method="post" class="mt-4">
                            <label for="status" class="form-label fw-bold">Update Status</label>
                            <select class="form-select mb-3" id="status" name="status">
                                <?php foreach ($allowedStatuses as $status): ?>
                                    <option value="<?php echo e($status); ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>>
                                        <?php echo e(ucfirst($status)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check2-circle me-1"></i> Update Status
                            </button>
                        </form>
                    </div>

                    <div class="order-info-card">
                        <h2 class="h5 fw-bold mb-3">Customer Details</h2>
                        <p><strong>Name:</strong> <?php echo e($order['full_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo e($order['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo e($order['phone']); ?></p>
                        <p><strong>Address:</strong> <?php echo e($order['address']); ?>, <?php echo e($order['city']); ?></p>
                        <p class="mb-0"><strong>Notes:</strong> <?php echo e($order['notes'] ?: 'No notes provided.'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
