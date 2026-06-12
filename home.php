<?php
$pageTitle = 'Home';
require_once 'includes/header.php';

$categoryStmt = $pdo->query('SELECT id, name, description FROM categories ORDER BY name');
$categories = $categoryStmt->fetchAll();

$featuredStmt = $pdo->prepare(
    'SELECT products.*, categories.name AS category_name
     FROM products
     INNER JOIN categories ON products.category_id = categories.id
     WHERE products.status = :status AND products.is_featured = 1
     ORDER BY products.created_at DESC
     LIMIT 6'
);
$featuredStmt->execute(['status' => 'active']);
$featuredProducts = $featuredStmt->fetchAll();

$categoryIcons = [
    'Vegetables' => 'bi-basket2-fill',
    'Fruits' => 'bi-tree-fill',
    'Dairy' => 'bi-cup-hot',
    'Coffee & Tea' => 'bi-cup-hot-fill',
    'Farm Products' => 'bi-basket2',
];
?>

<section class="hero-section d-flex align-items-center">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="hero-kicker">
                    <i class="bi bi-patch-check-fill"></i>
                    Organic farm produce
                </span>
                <h1 class="hero-title">Fresh Organic Products from Our Farm to Your Doorstep</h1>
                <p class="hero-text mb-4">
                    Shop vegetables, fruits, dairy, coffee, tea, honey, grains, and farm baskets grown with care at GreenHarvest Farm.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="products.php" class="btn btn-light btn-lg">
                        <i class="bi bi-basket me-2"></i>Shop Products
                    </a>
                    <a href="about.php" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-info-circle me-2"></i>Our Story
                    </a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="hero-panel">
                    <div class="row g-4">
                        <div class="col-6">
                            <div class="hero-stat">
                                <strong>14+</strong>
                                <span>Farm products</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="hero-stat">
                                <strong>5</strong>
                                <span>Categories</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="hero-stat">
                                <strong>100%</strong>
                                <span>Organic focus</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="hero-stat">
                                <strong>Fresh</strong>
                                <span>Daily harvest</span>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <p class="mb-0 text-muted">
                        Order online and receive carefully packed farm products at your doorstep.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="badge badge-soft mb-3">About GreenHarvest Farm</span>
                <h2 class="section-title mb-3">A modern farm shop built around freshness, trust, and direct delivery.</h2>
                <p class="text-muted">
                    GreenHarvest Farm connects customers directly with fresh organic products. The goal of this e-commerce platform is to make it easy for customers to browse farm products, place orders, and receive quality produce without middlemen.
                </p>
                <div class="row g-3 mt-2">
                    <div class="col-sm-6">
                        <div class="info-card p-3 h-100">
                            <i class="bi bi-shield-check text-success fs-3"></i>
                            <h3 class="h6 fw-bold mt-3">Quality First</h3>
                            <p class="text-muted small mb-0">Products are selected, packed, and handled carefully.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="info-card p-3 h-100">
                            <i class="bi bi-truck text-success fs-3"></i>
                            <h3 class="h6 fw-bold mt-3">Doorstep Delivery</h3>
                            <p class="text-muted small mb-0">Customers can order from home and receive farm products easily.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-visual">
                    <div class="about-visual-main">
                        <i class="bi bi-flower1"></i>
                        <span>GreenHarvest Farm</span>
                    </div>
                    <div class="about-visual-note">
                        <strong>Fresh today</strong>
                        <span>Vegetables, fruits, dairy, coffee, tea, honey, grains, and more.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge badge-soft mb-3">Shop by category</span>
            <h2 class="section-title">Farm Product Categories</h2>
            <p class="section-subtitle">Browse products by category and quickly find what your home needs.</p>
        </div>

        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
                <?php $icon = $categoryIcons[$category['name']] ?? 'bi-basket'; ?>
                <div class="col-sm-6 col-lg">
                    <a class="category-card d-block h-100" href="products.php?category=<?php echo e($category['id']); ?>">
                        <span class="category-icon"><i class="bi <?php echo e($icon); ?>"></i></span>
                        <h3 class="h5 fw-bold text-dark"><?php echo e($category['name']); ?></h3>
                        <p class="text-muted small mb-0"><?php echo e($category['description']); ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-5">
            <div>
                <span class="badge badge-soft mb-3">Featured products</span>
                <h2 class="section-title mb-2">Fresh Picks From The Farm</h2>
                <p class="text-muted mb-0">Popular products selected for the homepage from the database.</p>
            </div>
            <a href="products.php" class="btn btn-outline-success">
                View All Products <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php if (count($featuredProducts) > 0): ?>
                <?php foreach ($featuredProducts as $product): ?>
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
                                    <?php echo e(substr($product['description'], 0, 95)); ?>...
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
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info mb-0">No featured products are available yet.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="home-cta">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-2">Ready to order fresh farm products?</h2>
                <p class="mb-0">Browse our shop, add products to your cart, and complete checkout in a few simple steps.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="products.php" class="btn btn-light cta-button">
                    <i class="bi bi-bag-check me-2"></i>Start Shopping
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
