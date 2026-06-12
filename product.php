<?php
require_once 'includes/db.php';

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = null;
$relatedProducts = [];

if ($productId) {
    $stmt = $pdo->prepare(
        'SELECT products.*, categories.name AS category_name
         FROM products
         INNER JOIN categories ON products.category_id = categories.id
         WHERE products.id = :id AND products.status = :status
         LIMIT 1'
    );
    $stmt->execute([
        'id' => $productId,
        'status' => 'active',
    ]);
    $product = $stmt->fetch();
}

if ($product) {
    $relatedStmt = $pdo->prepare(
        'SELECT products.*, categories.name AS category_name
         FROM products
         INNER JOIN categories ON products.category_id = categories.id
         WHERE products.category_id = :category_id
           AND products.id != :product_id
           AND products.status = :status
         ORDER BY products.created_at DESC
         LIMIT 3'
    );
    $relatedStmt->execute([
        'category_id' => $product['category_id'],
        'product_id' => $product['id'],
        'status' => 'active',
    ]);
    $relatedProducts = $relatedStmt->fetchAll();
}

$pageTitle = $product ? $product['name'] : 'Product Not Found';
require_once 'includes/header.php';
?>

<?php if (!$product): ?>
    <section class="page-header">
        <div class="container">
            <span class="hero-kicker"><i class="bi bi-exclamation-circle"></i> Product not found</span>
            <h1 class="display-5 fw-bold mb-3">We could not find that product</h1>
            <p class="lead mb-0">The product may have been removed or the link may be incorrect.</p>
        </div>
    </section>

    <section class="section-padding">
        <div class="container">
            <div class="empty-state text-center">
                <i class="bi bi-basket"></i>
                <h2 class="h4 fw-bold mt-3">Product unavailable</h2>
                <p class="text-muted mb-4">Return to the shop and choose another fresh product.</p>
                <a href="products.php" class="btn btn-success">Back To Products</a>
            </div>
        </div>
    </section>
<?php else: ?>
    <section class="product-detail-section">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="home.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo e($product['name']); ?></li>
                </ol>
            </nav>

            <div class="row g-5 align-items-start">
                <div class="col-lg-6">
                    <div class="product-detail-image">
                        <?php if (!empty($product['image_path'])): ?>
                            <img src="<?php echo e($product['image_path']); ?>" alt="<?php echo e($product['name']); ?>">
                        <?php else: ?>
                            <div class="product-placeholder">
                                <i class="bi bi-image"></i>
                                <span>No image uploaded</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="product-detail-card">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge badge-soft"><?php echo e($product['category_name']); ?></span>
                            <?php if ((int) $product['is_featured'] === 1): ?>
                                <span class="badge text-bg-warning">Featured</span>
                            <?php endif; ?>
                            <?php if ((int) $product['stock'] > 0): ?>
                                <span class="badge text-bg-success">In Stock</span>
                            <?php else: ?>
                                <span class="badge text-bg-danger">Out of Stock</span>
                            <?php endif; ?>
                        </div>

                        <h1 class="product-detail-title"><?php echo e($product['name']); ?></h1>
                        <p class="product-detail-description"><?php echo e($product['description']); ?></p>

                        <div class="product-detail-price"><?php echo displayProductPrice($product); ?></div>

                        <div class="product-meta-grid">
                            <div>
                                <span>Category</span>
                                <strong><?php echo e($product['category_name']); ?></strong>
                            </div>
                            <div>
                                <span>Availability</span>
                                <strong><?php echo (int) $product['stock'] > 0 ? 'In stock' : 'Out of stock'; ?></strong>
                            </div>
                            <div>
                                <span>Payment</span>
                                <strong>COD, MTN MoMo, Airtel</strong>
                            </div>
                            <div>
                                <span>Delivery</span>
                                <strong>Doorstep order</strong>
                            </div>
                        </div>

                        <?php if ((int) $product['stock'] > 0): ?>
                            <form action="cart.php" method="post" class="add-cart-form js-add-to-cart-form">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo e($product['id']); ?>">
                                <input type="hidden" name="return_url" value="<?php echo e($_SERVER['REQUEST_URI']); ?>">

                                <div class="row g-3 align-items-end">
                                    <div class="col-sm-4">
                                        <label for="quantity" class="form-label fw-bold">Quantity</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            id="quantity"
                                            name="quantity"
                                            value="1"
                                            min="1"
                                            max="<?php echo e($product['stock']); ?>">
                                    </div>
                                    <div class="col-sm-8 d-grid">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="bi bi-bag-plus me-2"></i>Add To Cart
                                        </button>
                                    </div>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                This product is currently out of stock.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if (count($relatedProducts) > 0): ?>
        <section class="section-padding bg-white">
            <div class="container">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-5">
                    <div>
                        <span class="badge badge-soft mb-3">Related products</span>
                        <h2 class="section-title mb-2">More From <?php echo e($product['category_name']); ?></h2>
                        <p class="text-muted mb-0">Customers also browse these farm products.</p>
                    </div>
                    <a href="products.php?category=<?php echo e($product['category_id']); ?>" class="btn btn-outline-success">
                        View Category <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="row g-4">
                    <?php foreach ($relatedProducts as $related): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="product-card clickable-card" data-href="product.php?id=<?php echo e($related['id']); ?>" role="link" tabindex="0" aria-label="View details for <?php echo e($related['name']); ?>">
                                <div class="product-image-wrap">
                                    <?php if (!empty($related['image_path'])): ?>
                                        <img src="<?php echo e($related['image_path']); ?>" alt="<?php echo e($related['name']); ?>">
                                    <?php else: ?>
                                        <div class="product-placeholder">
                                            <i class="bi bi-image"></i>
                                            <span>No image uploaded</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="p-4">
                                    <span class="badge badge-soft mb-2"><?php echo e($related['category_name']); ?></span>
                                    <h3 class="h5 fw-bold"><?php echo e($related['name']); ?></h3>
                                    <p class="text-muted small product-summary">
                                        <?php echo e(substr($related['description'], 0, 100)); ?>...
                                    </p>
                                    <div class="product-card-footer">
                                        <span class="price"><?php echo displayProductPrice($related); ?></span>
                                        <div class="product-card-actions card-action">
                                            <a href="product.php?id=<?php echo e($related['id']); ?>" class="btn btn-outline-success btn-sm">Details</a>
                                            <?php if ((int) $related['stock'] > 0): ?>
                                                <form action="cart.php" method="post" class="m-0 js-add-to-cart-form">
                                                    <input type="hidden" name="action" value="add">
                                                    <input type="hidden" name="product_id" value="<?php echo e($related['id']); ?>">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <input type="hidden" name="return_url" value="<?php echo e($_SERVER['REQUEST_URI']); ?>">
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="bi bi-bag-plus me-1"></i>Add
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-secondary btn-sm" disabled>Unavailable</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
