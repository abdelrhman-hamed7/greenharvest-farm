<?php
require_once 'includes/db.php';

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$order = null;
$orderItems = [];
if ($orderId) {
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

    if ($order) {
        $itemsStmt = $pdo->prepare(
            'SELECT *
             FROM order_items
             WHERE order_id = :order_id
             ORDER BY id ASC'
        );
        $itemsStmt->execute(['order_id' => $orderId]);
        $orderItems = $itemsStmt->fetchAll();
    }
}

$pageTitle = 'Order Success';
require_once 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <span class="hero-kicker"><i class="bi bi-check-circle-fill"></i> Order confirmation</span>
        <h1 class="display-5 fw-bold mb-3">Thank You For Your Order</h1>
        <p class="lead mb-0">Your GreenHarvest Farm order has been received.</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <?php if ($order): ?>
            <div class="success-card">
                <div class="text-center">
                    <div class="success-icon"><i class="bi bi-check2"></i></div>
                    <h2 class="h3 fw-bold mt-3">Order Placed Successfully</h2>
                    <p class="text-muted mb-4">
                        Thank you, <?php echo e($order['full_name']); ?>. We will contact you through <?php echo e($order['email']); ?> to confirm delivery.
                    </p>
                </div>

                <div class="success-details mb-4">
                    <div>
                        <span>Order Number</span>
                        <strong><?php echo e($order['order_number']); ?></strong>
                    </div>
                    <div>
                        <span>Total Amount</span>
                        <strong><?php echo formatPrice($order['total_amount']); ?></strong>
                    </div>
                    <div>
                        <span>Status</span>
                        <strong><?php echo e(ucfirst($order['status'])); ?></strong>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="confirmation-box h-100">
                            <h3 class="h5 fw-bold mb-3">Customer Details</h3>
                            <p class="mb-2"><strong>Name:</strong> <?php echo e($order['full_name']); ?></p>
                            <p class="mb-2"><strong>Email:</strong> <?php echo e($order['email']); ?></p>
                            <p class="mb-2"><strong>Phone:</strong> <?php echo e($order['phone']); ?></p>
                            <p class="mb-0"><strong>Delivery:</strong> <?php echo e($order['address']); ?>, <?php echo e($order['city']); ?></p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="confirmation-box h-100">
                            <h3 class="h5 fw-bold mb-3">Payment And Delivery</h3>
                            <p class="mb-2"><strong>Payment:</strong> <?php echo e($order['payment_method']); ?></p>
                            <?php if (!empty($order['payment_phone'])): ?>
                                <p class="mb-2"><strong>Payment Phone:</strong> <?php echo e($order['payment_phone']); ?></p>
                            <?php endif; ?>
                            <p class="mb-2"><strong>Payment Status:</strong> <?php echo e(ucfirst($order['payment_status'] ?? 'pending')); ?></p>
                            <p class="mb-2"><strong>Order Date:</strong> <?php echo e(date('M d, Y h:i A', strtotime($order['created_at']))); ?></p>
                            <p class="mb-0"><strong>Notes:</strong> <?php echo e($order['notes'] ?: 'No special notes.'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="confirmation-box mt-4">
                    <h3 class="h5 fw-bold mb-3">Purchased Items</h3>
                    <?php foreach ($orderItems as $item): ?>
                        <div class="confirmation-item">
                            <div>
                                <strong><?php echo e($item['product_name']); ?></strong>
                                <span><?php echo e($item['quantity']); ?> x <?php echo formatPrice($item['price']); ?></span>
                            </div>
                            <strong><?php echo formatPrice($item['subtotal']); ?></strong>
                        </div>
                    <?php endforeach; ?>

                    <div class="confirmation-total">
                        <span>Total Amount</span>
                        <strong><?php echo formatPrice($order['total_amount']); ?></strong>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
                    <a href="products.php" class="btn btn-success">
                        <i class="bi bi-basket me-1"></i> Continue Shopping
                    </a>
                    <button type="button" class="btn btn-outline-success" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print Confirmation
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state text-center">
                <i class="bi bi-exclamation-circle"></i>
                <h2 class="h4 fw-bold mt-3">Order not found</h2>
                <p class="text-muted mb-4">The order confirmation link is invalid or expired.</p>
                <a href="products.php" class="btn btn-success">Back To Products</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
