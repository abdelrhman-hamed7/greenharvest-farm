<?php
$pageTitle = 'Products';
require_once 'includes/header.php';

$selectedCategory = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);
if ($selectedCategory === false) {
    $selectedCategory = null;
}

$search = trim($_GET['search'] ?? '');

$categoryStmt = $pdo->query('SELECT id, name FROM categories ORDER BY name');
$categories = $categoryStmt->fetchAll();

$sql = 'SELECT products.*, categories.name AS category_name
        FROM products
        INNER JOIN categories ON products.category_id = categories.id
        WHERE products.status = :status';

$params = ['status' => 'active'];

if (!empty($selectedCategory)) {
    $sql .= ' AND products.category_id = :category_id';
    $params['category_id'] = $selectedCategory;
}

if ($search !== '') {
    $sql .= ' AND (products.name LIKE :search_name OR products.description LIKE :search_description)';
    $params['search_name'] = '%' . $search . '%';
    $params['search_description'] = '%' . $search . '%';
}

$sql .= ' ORDER BY products.created_at DESC';

$productStmt = $pdo->prepare($sql);
$productStmt->execute($params);
$products = $productStmt->fetchAll();
?>

<section class="page-header">
    <div class="container">
        <span class="hero-kicker"><i class="bi bi-basket2-fill"></i> Farm shop</span>
        <h1 class="display-5 fw-bold mb-3">Shop Fresh Organic Products</h1>
        <p class="lead mb-0">Browse vegetables, fruits, dairy, coffee, tea, and farm essentials directly from GreenHarvest Farm.</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="filter-panel mb-4">
            <form action="products.php" method="get" class="row g-3 align-items-end">
                <div class="col-lg-5">
                    <label for="search" class="form-label fw-bold">Search products</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input
                            type="text"
                            class="form-control"
                            id="search"
                            name="search"
                            value="<?php echo e($search); ?>"
                            placeholder="Search tomatoes, honey, coffee...">
                    </div>
                </div>

                <div class="col-lg-4">
                    <label for="category" class="form-label fw-bold">Filter by category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo e($category['id']); ?>" <?php echo (int) $selectedCategory === (int) $category['id'] ? 'selected' : ''; ?>>
                                <?php echo e($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-lg-3 d-grid d-sm-flex gap-2">
                    <button type="submit" class="btn btn-success flex-fill">
                        <i class="bi bi-funnel me-1"></i> Apply
                    </button>
                    <a href="products.php" class="btn btn-outline-success flex-fill">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="product-toolbar mb-4">
            <div>
                <h2 class="h4 fw-bold mb-1">Available Products</h2>
                <p class="text-muted mb-0">
                    Showing <?php echo e(count($products)); ?> product<?php echo count($products) === 1 ? '' : 's'; ?>.
                </p>
            </div>
            <a href="cart.php" class="btn btn-outline-success">
                <i class="bi bi-bag me-1"></i> View Cart
            </a>
        </div>

        <?php if (count($products) > 0): ?>
            <div class="row g-4">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="product-card clickable-card" data-href="product.php?id=<?php echo e($product['id']); ?>" role="link" tabindex="0" aria-label="View details for <?php echo e($product['name']); ?>">
                            <div class="product-image-wrap">
                                <?php if (!empty($product['image_path'])): ?>
                                    <img src="<?php echo e($product['image_path']); ?>" alt="<?php echo e($product['name']); ?>">
                                <?php else: ?>
                                    <div class="product-placeholder">
                                        <i class="bi bi-image"></i>
                                        <span>No image uploaded</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge badge-soft"><?php echo e($product['category_name']); ?></span>
                                    <?php if ((int) $product['stock'] > 0): ?>
                                        <span class="small text-success fw-bold">In stock</span>
                                    <?php else: ?>
                                        <span class="small text-danger fw-bold">Out of stock</span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="h5 fw-bold"><?php echo e($product['name']); ?></h3>
                                <p class="text-muted small product-summary">
                                    <?php echo e(substr($product['description'], 0, 110)); ?>...
                                </p>
                                <div class="product-card-footer">
                                    <span class="price"><?php echo displayProductPrice($product); ?></span>
                                    <div class="product-card-actions card-action">
                                        <a href="product.php?id=<?php echo e($product['id']); ?>" class="btn btn-outline-success btn-sm">
                                            Details
                                        </a>
                                        <?php if ((int) $product['stock'] > 0): ?>
                                            <form action="cart.php" method="post" class="m-0 js-add-to-cart-form">
                                                <input type="hidden" name="action" value="add">
                                                <input type="hidden" name="product_id" value="<?php echo e($product['id']); ?>">
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
        <?php else: ?>
            <div class="empty-state text-center">
                <i class="bi bi-search"></i>
                <h2 class="h4 fw-bold mt-3">No products found</h2>
                <p class="text-muted mb-4">Try another search keyword or select a different category.</p>
                <a href="products.php" class="btn btn-success">Show All Products</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
