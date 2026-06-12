<?php
require_once 'includes/db.php';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function loadCheckoutCart(PDO $pdo, array $cart)
{
    $items = [];
    $total = 0;

    if (empty($cart)) {
        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    $productIds = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));

    $stmt = $pdo->prepare(
        "SELECT products.*, categories.name AS category_name
         FROM products
         INNER JOIN categories ON products.category_id = categories.id
         WHERE products.id IN ($placeholders) AND products.status = 'active'"
    );
    $stmt->execute($productIds);
    $products = $stmt->fetchAll();

    foreach ($products as $product) {
        $productId = (int) $product['id'];
        $quantity = (int) ($cart[$productId] ?? 0);

        if ($quantity < 1 || (int) $product['stock'] < 1) {
            continue;
        }

        $quantity = min($quantity, (int) $product['stock']);
        $subtotal = $quantity * (float) $product['price'];
        $total += $subtotal;

        $items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
        ];
    }

    return [
        'items' => $items,
        'total' => $total,
    ];
}

function generateOrderNumber()
{
    return 'GH-' . date('Ymd-His') . '-' . random_int(1000, 9999);
}

$paymentMethods = [
    'Cash on Delivery' => [
        'icon' => 'bi-cash-stack',
        'title' => 'Cash on Delivery',
        'description' => 'Pay when your farm products arrive.',
    ],
    'MTN MoMo' => [
        'logo' => 'assets/payments/mtn-momo.png',
        'logo_class' => 'payment-logo-wide',
        'title' => 'MTN MoMo',
        'description' => 'Pay using your MTN Mobile Money number.',
    ],
    'Airtel Money' => [
        'logo' => 'assets/payments/airtel-money.png',
        'logo_class' => 'payment-logo-square',
        'title' => 'Airtel Money',
        'description' => 'Pay using your Airtel Money number.',
    ],
];

$cartData = loadCheckoutCart($pdo, $_SESSION['cart']);
$cartItems = $cartData['items'];
$cartTotal = $cartData['total'];

$account = null;
if (!empty($_SESSION['user_logged_in']) && !empty($_SESSION['user_id'])) {
    $accountStmt = $pdo->prepare(
        'SELECT full_name, email, phone, address, city
         FROM customer_accounts
         WHERE id = :id
         LIMIT 1'
    );
    $accountStmt->execute(['id' => (int) $_SESSION['user_id']]);
    $account = $accountStmt->fetch();
}

$errors = [];
$formData = [
    'full_name' => $account['full_name'] ?? '',
    'email' => $account['email'] ?? '',
    'phone' => $account['phone'] ?? '',
    'address' => $account['address'] ?? '',
    'city' => $account['city'] ?? '',
    'notes' => '',
    'payment_method' => 'Cash on Delivery',
    'payment_phone' => $account['phone'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($formData as $field => $value) {
        $formData[$field] = trim($_POST[$field] ?? '');
    }

    if ($account) {
        foreach (['full_name', 'email', 'phone', 'address', 'city'] as $field) {
            if ($formData[$field] === '') {
                $formData[$field] = $account[$field] ?? '';
            }
        }
    }

    if ($formData['full_name'] === '') {
        $errors[] = 'Full name is required.';
    }

    if ($formData['email'] === '' || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    if ($formData['phone'] === '') {
        $errors[] = 'Phone number is required.';
    }

    if ($formData['address'] === '') {
        $errors[] = 'Delivery address is required.';
    }

    if ($formData['city'] === '') {
        $errors[] = 'City is required.';
    }

    if (!array_key_exists($formData['payment_method'], $paymentMethods)) {
        $errors[] = 'Please select a valid payment method.';
    }

    if ($formData['payment_method'] !== 'Cash on Delivery') {
        if ($formData['payment_phone'] === '') {
            $errors[] = 'Payment phone number is required for mobile money.';
        } elseif (!preg_match('/^[0-9+ ]{10,20}$/', $formData['payment_phone'])) {
            $errors[] = 'Please enter a valid mobile money phone number.';
        }
    }

    if (empty($cartItems)) {
        $errors[] = 'Your cart is empty. Please add products before checkout.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $productIds = array_keys($_SESSION['cart']);
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
            $stockStmt = $pdo->prepare(
                "SELECT id, name, price, stock
                 FROM products
                 WHERE id IN ($placeholders) AND status = 'active'
                 FOR UPDATE"
            );
            $stockStmt->execute($productIds);
            $lockedProducts = $stockStmt->fetchAll();

            $lockedProductsById = [];
            foreach ($lockedProducts as $lockedProduct) {
                $lockedProductsById[(int) $lockedProduct['id']] = $lockedProduct;
            }

            $orderItems = [];
            $orderTotal = 0;

            foreach ($_SESSION['cart'] as $productId => $quantity) {
                $productId = (int) $productId;
                $quantity = (int) $quantity;

                if (!isset($lockedProductsById[$productId])) {
                    throw new Exception('One of the products is no longer available.');
                }

                $product = $lockedProductsById[$productId];

                if ($quantity < 1) {
                    throw new Exception('Invalid product quantity found in cart.');
                }

                if ($quantity > (int) $product['stock']) {
                    throw new Exception($product['name'] . ' does not have enough stock.');
                }

                $subtotal = $quantity * (float) $product['price'];
                $orderTotal += $subtotal;

                $orderItems[] = [
                    'product_id' => $productId,
                    'product_name' => $product['name'],
                    'quantity' => $quantity,
                    'price' => (float) $product['price'],
                    'subtotal' => $subtotal,
                ];
            }

            if (empty($orderItems)) {
                throw new Exception('Your cart is empty.');
            }

            $customerStmt = $pdo->prepare(
                'INSERT INTO customers (full_name, email, phone, address, city, notes)
                 VALUES (:full_name, :email, :phone, :address, :city, :notes)'
            );
            $customerStmt->execute([
                'full_name' => $formData['full_name'],
                'email' => $formData['email'],
                'phone' => $formData['phone'],
                'address' => $formData['address'],
                'city' => $formData['city'],
                'notes' => $formData['notes'],
            ]);
            $customerId = (int) $pdo->lastInsertId();

            $orderStmt = $pdo->prepare(
                'INSERT INTO orders (customer_id, order_number, total_amount, status, payment_method, payment_phone, payment_status)
                 VALUES (:customer_id, :order_number, :total_amount, :status, :payment_method, :payment_phone, :payment_status)'
            );
            $orderStmt->execute([
                'customer_id' => $customerId,
                'order_number' => generateOrderNumber(),
                'total_amount' => $orderTotal,
                'status' => 'pending',
                'payment_method' => $formData['payment_method'],
                'payment_phone' => $formData['payment_method'] === 'Cash on Delivery' ? null : $formData['payment_phone'],
                'payment_status' => 'pending',
            ]);
            $orderId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal)
                 VALUES (:order_id, :product_id, :product_name, :quantity, :price, :subtotal)'
            );
            $stockUpdateStmt = $pdo->prepare(
                'UPDATE products SET stock = stock - :quantity WHERE id = :product_id'
            );

            foreach ($orderItems as $item) {
                $itemStmt->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);

                $stockUpdateStmt->execute([
                    'quantity' => $item['quantity'],
                    'product_id' => $item['product_id'],
                ]);
            }

            $pdo->commit();

            $_SESSION['cart'] = [];
            $_SESSION['last_order_id'] = $orderId;

            header('Location: order-success.php?id=' . $orderId);
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errors[] = $e->getMessage();
        }
    }
}

$pageTitle = 'Checkout';
require_once 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <span class="hero-kicker"><i class="bi bi-credit-card-2-front-fill"></i> Checkout</span>
        <h1 class="display-5 fw-bold mb-3">Complete Your Order</h1>
        <p class="lead mb-0">Enter your delivery details and confirm your GreenHarvest Farm order.</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="empty-state text-center">
                <i class="bi bi-bag"></i>
                <h2 class="h4 fw-bold mt-3">Your cart is empty</h2>
                <p class="text-muted mb-4">Add products to your cart before going to checkout.</p>
                <a href="products.php" class="btn btn-success">Shop Products</a>
            </div>
        <?php else: ?>
            <form action="checkout.php" method="post" class="row g-4">
                <div class="col-lg-8">
                    <div class="checkout-card">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-4">
                            <h2 class="h4 fw-bold mb-0">Customer Details</h2>
                            <?php if ($account): ?>
                                <span class="badge badge-soft align-self-md-center">
                                    <i class="bi bi-person-check me-1"></i>Filled from your account
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label fw-bold">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo e($formData['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo e($formData['email']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-bold">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo e($formData['phone']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label fw-bold">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo e($formData['city']); ?>" required>
                            </div>
                            <div class="col-12">
                                <label for="address" class="form-label fw-bold">Delivery Address</label>
                                <input type="text" class="form-control" id="address" name="address" value="<?php echo e($formData['address']); ?>" required>
                            </div>
                            <div class="col-12">
                                <label for="notes" class="form-label fw-bold">Order Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Optional delivery instructions"><?php echo e($formData['notes']); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-card mt-4">
                        <h2 class="h4 fw-bold mb-4">Payment Method</h2>

                        <div class="payment-method-grid">
                            <?php foreach ($paymentMethods as $methodValue => $method): ?>
                                <label class="payment-option">
                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="<?php echo e($methodValue); ?>"
                                        data-payment-label="<?php echo e($method['title']); ?>"
                                        <?php echo $formData['payment_method'] === $methodValue ? 'checked' : ''; ?>>
                                    <span class="payment-option-body">
                                        <?php if (!empty($method['logo'])): ?>
                                            <img
                                                class="payment-logo <?php echo e($method['logo_class'] ?? ''); ?>"
                                                src="<?php echo e($method['logo']); ?>"
                                                alt="<?php echo e($method['title']); ?> logo">
                                        <?php else: ?>
                                            <i class="bi <?php echo e($method['icon']); ?>"></i>
                                        <?php endif; ?>
                                        <strong><?php echo e($method['title']); ?></strong>
                                        <small><?php echo e($method['description']); ?></small>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <div class="mt-3 js-mobile-money-field <?php echo $formData['payment_method'] === 'Cash on Delivery' ? 'd-none' : ''; ?>">
                            <label for="payment_phone" class="form-label fw-bold">Mobile Money Phone Number</label>
                            <input
                                type="text"
                                class="form-control"
                                id="payment_phone"
                                name="payment_phone"
                                value="<?php echo e($formData['payment_phone']); ?>"
                                placeholder="+2507XXXXXXXX">
                            <small class="text-muted">Used only for MTN MoMo or Airtel Money payment confirmation.</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="order-summary-card">
                        <h2 class="h4 fw-bold mb-4">Order Summary</h2>

                        <?php foreach ($cartItems as $item): ?>
                            <?php $product = $item['product']; ?>
                            <?php $unitLabel = priceUnitLabel($product); ?>
                            <div class="checkout-item">
                                <div>
                                    <strong><?php echo e($product['name']); ?></strong>
                                    <span>
                                        Qty: <?php echo e($item['quantity']); ?> |
                                        Unit price: <?php echo formatPrice($product['price']); ?>
                                        <?php if ($unitLabel !== ''): ?>
                                            <small class="unit-note"><?php echo e($unitLabel); ?></small>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <span><?php echo formatPrice($item['subtotal']); ?></span>
                            </div>
                        <?php endforeach; ?>

                        <div class="summary-row">
                            <span>Payment Method</span>
                            <strong class="js-selected-payment-label"><?php echo e($formData['payment_method']); ?></strong>
                        </div>
                        <div class="summary-row">
                            <span>Payment Status</span>
                            <strong>Pending</strong>
                        </div>
                        <div class="summary-row summary-total">
                            <span>Total</span>
                            <strong><?php echo formatPrice($cartTotal); ?></strong>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check2-circle me-2"></i>Place Order
                            </button>
                            <a href="cart.php" class="btn btn-outline-success">Back To Cart</a>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
