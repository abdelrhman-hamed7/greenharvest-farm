<?php
require_once '../includes/admin-auth.php';
require_once '../includes/product-image.php';
requireAdmin();

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$productId) {
    header('Location: products.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT products.*, categories.name AS category_name
     FROM products
     INNER JOIN categories ON products.category_id = categories.id
     WHERE products.id = :id
     LIMIT 1'
);
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['admin_message'] = [
        'type' => 'danger',
        'text' => 'Product not found.',
    ];
    header('Location: products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deleteStmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
    $deleteStmt->execute(['id' => $productId]);

    if (!empty($product['image_path'])) {
        removeProductImage($product['image_path']);
    }

    $_SESSION['admin_message'] = [
        'type' => 'success',
        'text' => 'Product deleted successfully.',
    ];

    header('Location: products.php');
    exit;
}

$pageTitle = 'Delete Product';
require_once '../includes/header.php';
?>

<section class="admin-page-header">
    <div class="container">
        <span class="badge badge-soft mb-3">Product management</span>
        <h1 class="display-6 fw-bold mb-2">Delete Product</h1>
        <p class="text-muted mb-0">Confirm before removing this product from the catalog.</p>
    </div>
</section>

<section class="admin-shell">
    <div class="container">
        <div class="delete-card">
            <div class="delete-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <h2 class="h4 fw-bold mt-3">Delete <?php echo e($product['name']); ?>?</h2>
            <p class="text-muted">
                This will remove the product from the public shop. Existing order history will keep the product name inside order items.
            </p>

            <div class="delete-product-preview">
                <div class="admin-product-thumb">
                    <?php $imageSrc = productImageSrc($product, '../'); ?>
                    <?php if ($imageSrc !== ''): ?>
                        <img src="<?php echo e($imageSrc); ?>" alt="<?php echo e($product['name']); ?>">
                    <?php else: ?>
                        <i class="bi bi-image"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <strong><?php echo e($product['name']); ?></strong>
                    <span><?php echo e($product['category_name']); ?> | <?php echo formatPrice($product['price']); ?></span>
                </div>
            </div>

            <form action="delete-product.php?id=<?php echo e($productId); ?>" method="post" class="d-flex flex-wrap gap-2 justify-content-center mt-4">
                <a href="products.php" class="btn btn-outline-success">Cancel</a>
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash me-1"></i> Delete Product
                </button>
            </form>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
