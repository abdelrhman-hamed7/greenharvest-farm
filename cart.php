<?php
require_once 'includes/db.php';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function setCartMessage($type, $message)
{
    $_SESSION['cart_message'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function setFlashMessage($type, $message)
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function currentCartCount()
{
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0;
    }

    return array_sum($_SESSION['cart']);
}

function isAjaxRequest()
{
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
}

function sendCartJson($success, $type, $message)
{
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'type' => $type,
        'message' => $message,
        'cart_count' => currentCartCount(),
    ]);
    exit;
}

function redirectToCart()
{
    header('Location: cart.php');
    exit;
}

function redirectAfterAdd()
{
    $returnUrl = $_POST['return_url'] ?? ($_SERVER['HTTP_REFERER'] ?? 'products.php');
    $path = parse_url($returnUrl, PHP_URL_PATH);
    $query = parse_url($returnUrl, PHP_URL_QUERY);
    $allowedPages = ['home.php', 'products.php', 'product.php', 'about.php'];
    $page = $path ? basename($path) : '';

    if (in_array($page, $allowedPages, true)) {
        header('Location: ' . $page . ($query ? '?' . $query : ''));
        exit;
    }

    header('Location: products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

        if (!$productId || !$quantity || $quantity < 1) {
            if (isAjaxRequest()) {
                sendCartJson(false, 'danger', 'Please choose a valid product quantity.');
            }

            setFlashMessage('danger', 'Please choose a valid product quantity.');
            redirectAfterAdd();
        }

        $stmt = $pdo->prepare('SELECT id, name, stock FROM products WHERE id = :id AND status = :status LIMIT 1');
        $stmt->execute([
            'id' => $productId,
            'status' => 'active',
        ]);
        $product = $stmt->fetch();

        if (!$product || (int) $product['stock'] < 1) {
            if (isAjaxRequest()) {
                sendCartJson(false, 'danger', 'This product is not available right now.');
            }

            setFlashMessage('danger', 'This product is not available right now.');
            redirectAfterAdd();
        }

        $currentQuantity = (int) ($_SESSION['cart'][$productId] ?? 0);
        $newQuantity = $currentQuantity + $quantity;

        if ($newQuantity > (int) $product['stock']) {
            $newQuantity = (int) $product['stock'];
            $messageType = 'warning';
            $messageText = 'Quantity adjusted to available stock for ' . $product['name'] . '.';
        } else {
            $messageType = 'success';
            $messageText = $product['name'] . ' was added to your cart.';
        }

        $_SESSION['cart'][$productId] = $newQuantity;

        if (isAjaxRequest()) {
            sendCartJson(true, $messageType, $messageText);
        }

        setFlashMessage($messageType, $messageText);
        redirectAfterAdd();
    }

    if ($action === 'update') {
        $quantities = $_POST['quantities'] ?? [];

        if (!is_array($quantities)) {
            setCartMessage('danger', 'Invalid cart update request.');
            redirectToCart();
        }

        foreach ($quantities as $productId => $quantity) {
            $productId = filter_var($productId, FILTER_VALIDATE_INT);
            $quantity = filter_var($quantity, FILTER_VALIDATE_INT);

            if (!$productId) {
                continue;
            }

            if (!$quantity || $quantity < 1) {
                unset($_SESSION['cart'][$productId]);
                continue;
            }

            $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = :id AND status = :status LIMIT 1');
            $stmt->execute([
                'id' => $productId,
                'status' => 'active',
            ]);
            $product = $stmt->fetch();

            if (!$product) {
                unset($_SESSION['cart'][$productId]);
                continue;
            }

            $_SESSION['cart'][$productId] = min($quantity, (int) $product['stock']);
        }

        setCartMessage('success', 'Your cart has been updated.');
        redirectToCart();
    }

    if ($action === 'remove') {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

        if ($productId) {
            unset($_SESSION['cart'][$productId]);
            setCartMessage('success', 'Product removed from your cart.');
        }

        redirectToCart();
    }

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        setCartMessage('success', 'Your cart has been cleared.');
        redirectToCart();
    }
}

$cartItems = [];
$cartTotal = 0;

if (!empty($_SESSION['cart'])) {
    $productIds = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));

    $stmt = $pdo->prepare(
        "SELECT products.*, categories.name AS category_name
         FROM products
         INNER JOIN categories ON products.category_id = categories.id
         WHERE products.id IN ($placeholders) AND products.status = 'active'"
    );
    $stmt->execute($productIds);
    $products = $stmt->fetchAll();
    $foundProductIds = [];

    foreach ($products as $product) {
        $productId = (int) $product['id'];
        $foundProductIds[] = $productId;
        $quantity = (int) ($_SESSION['cart'][$productId] ?? 0);

        if ($quantity < 1) {
            unset($_SESSION['cart'][$productId]);
            continue;
        }

        if ($quantity > (int) $product['stock']) {
            $quantity = (int) $product['stock'];
            $_SESSION['cart'][$productId] = $quantity;
        }

        if ($quantity < 1) {
            unset($_SESSION['cart'][$productId]);
            continue;
        }

        $subtotal = $quantity * (float) $product['price'];
        $cartTotal += $subtotal;

        $cartItems[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
        ];
    }

    foreach ($productIds as $productId) {
        if (!in_array((int) $productId, $foundProductIds, true)) {
            unset($_SESSION['cart'][$productId]);
        }
    }
}

$message = $_SESSION['cart_message'] ?? null;
unset($_SESSION['cart_message']);

$pageTitle = 'Shopping Cart';
require_once 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <span class="hero-kicker"><i class="bi bi-bag-check-fill"></i> Shopping cart</span>
        <h1 class="display-5 fw-bold mb-3">Review Your Farm Order</h1>
        <p class="lead mb-0">Update quantities, remove products, and continue to checkout when you are ready.</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo e($message['type']); ?> alert-dismissible fade show" role="alert" data-auto-dismiss="true">
                <?php echo e($message['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (count($cartItems) > 0): ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <form action="cart.php" method="post" class="cart-card">
                        <input type="hidden" name="action" value="update">

                        <div class="cart-header">
                            <h2 class="h4 fw-bold mb-0">Cart Items</h2>
                            <span class="text-muted"><?php echo e(count($cartItems)); ?> item<?php echo count($cartItems) === 1 ? '' : 's'; ?></span>
                        </div>

                        <?php foreach ($cartItems as $item): ?>
                            <?php $product = $item['product']; ?>
                            <div class="cart-item">
                                <div class="cart-item-image">
                                    <?php $imageSrc = productImageSrc($product); ?>
                                    <?php if ($imageSrc !== ''): ?>
                                        <img src="<?php echo e($imageSrc); ?>" alt="<?php echo e($product['name']); ?>">
                                    <?php else: ?>
                                        <i class="bi bi-image"></i>
                                    <?php endif; ?>
                                </div>

                                <div class="cart-item-body">
                                    <div>
                                        <span class="badge badge-soft mb-2"><?php echo e($product['category_name']); ?></span>
                                        <h3 class="h5 fw-bold mb-1">
                                            <a href="product.php?id=<?php echo e($product['id']); ?>"><?php echo e($product['name']); ?></a>
                                        </h3>
                                        <?php $unitLabel = priceUnitLabel($product); ?>
                                        <p class="text-muted small mb-0">
                                            Price: <?php echo formatPrice($product['price']); ?>
                                            <?php if ($unitLabel !== ''): ?>
                                                <span class="unit-note"><?php echo e($unitLabel); ?></span>
                                            <?php endif; ?>
                                        </p>
                                    </div>

                                    <div class="cart-item-actions">
                                        <label class="form-label fw-bold small" for="quantity-<?php echo e($product['id']); ?>">Quantity</label>
                                        <input
                                            type="number"
                                            class="form-control cart-quantity"
                                            id="quantity-<?php echo e($product['id']); ?>"
                                            name="quantities[<?php echo e($product['id']); ?>]"
                                            value="<?php echo e($item['quantity']); ?>"
                                            min="1"
                                            max="<?php echo e($product['stock']); ?>">
                                    </div>

                                    <div class="cart-item-total">
                                        <span>Subtotal</span>
                                        <strong><?php echo formatPrice($item['subtotal']); ?></strong>
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    form="remove-item-<?php echo e($product['id']); ?>"
                                    class="btn btn-outline-danger btn-sm cart-remove"
                                    data-bs-toggle="tooltip"
                                    title="Remove item">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>

                        <div class="cart-actions">
                            <a href="products.php" class="btn btn-outline-success">
                                <i class="bi bi-arrow-left me-1"></i> Continue Shopping
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-arrow-repeat me-1"></i> Update Cart
                            </button>
                        </div>
                    </form>

                    <?php foreach ($cartItems as $item): ?>
                        <?php $product = $item['product']; ?>
                        <form id="remove-item-<?php echo e($product['id']); ?>" action="cart.php" method="post" class="d-none">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?php echo e($product['id']); ?>">
                        </form>
                    <?php endforeach; ?>
                </div>

                <div class="col-lg-4">
                    <div class="order-summary-card">
                        <h2 class="h4 fw-bold mb-4">Order Summary</h2>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <strong><?php echo formatPrice($cartTotal); ?></strong>
                        </div>
                        <div class="summary-row">
                            <span>Delivery</span>
                            <strong>Cash on delivery</strong>
                        </div>
                        <div class="summary-row summary-total">
                            <span>Total</span>
                            <strong><?php echo formatPrice($cartTotal); ?></strong>
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <a href="checkout.php" class="btn btn-success btn-lg">
                                <i class="bi bi-credit-card me-2"></i>Proceed To Checkout
                            </a>
                            <form action="cart.php" method="post">
                                <input type="hidden" name="action" value="clear">
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    Clear Cart
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state text-center">
                <i class="bi bi-bag"></i>
                <h2 class="h4 fw-bold mt-3">Your cart is empty</h2>
                <p class="text-muted mb-4">Browse fresh products and add your favorites to the cart.</p>
                <a href="products.php" class="btn btn-success compact-button">
                    <i class="bi bi-basket me-1"></i> Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
