<?php
require_once '../includes/admin-auth.php';
requireAdmin();

$message = $_SESSION['admin_message'] ?? null;
unset($_SESSION['admin_message']);

$stmt = $pdo->query(
    'SELECT products.*, categories.name AS category_name
     FROM products
     INNER JOIN categories ON products.category_id = categories.id
     ORDER BY products.created_at DESC'
);
$products = $stmt->fetchAll();

$pageTitle = 'Manage Products';
require_once '../includes/header.php';
?>

<section class="admin-page-header">
    <div class="container">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3">
            <div>
                <span class="badge badge-soft mb-3">Product management</span>
                <h1 class="display-6 fw-bold mb-2">Products</h1>
                <p class="text-muted mb-0">Add, edit, delete, and review product images for GreenHarvest Farm.</p>
            </div>
            <a href="add-product.php" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i> Add Product
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

        <div class="admin-panel">
            <div class="admin-panel-header">
                <div>
                    <h2 class="h4 fw-bold mb-1">All Products</h2>
                    <p class="text-muted mb-0"><?php echo e(count($products)); ?> product<?php echo count($products) === 1 ? '' : 's'; ?> in the catalog.</p>
                </div>
                <a href="../products.php" class="btn btn-outline-success btn-sm">View Public Shop</a>
            </div>

            <?php if (count($products) > 0): ?>
                <div class="table-responsive">
                    <table class="table admin-products-table mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Featured</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <div class="admin-product-cell">
                                            <div class="admin-product-thumb">
                                                <?php if (!empty($product['image_path'])): ?>
                                                    <img src="../<?php echo e($product['image_path']); ?>" alt="<?php echo e($product['name']); ?>">
                                                <?php else: ?>
                                                    <i class="bi bi-image"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <strong><?php echo e($product['name']); ?></strong>
                                                <span><?php echo e(substr($product['description'], 0, 70)); ?>...</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo e($product['category_name']); ?></td>
                                    <td><?php echo formatPrice($product['price']); ?></td>
                                    <td><?php echo e($product['stock']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $product['status'] === 'active' ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                            <?php echo e(ucfirst($product['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo (int) $product['is_featured'] === 1 ? 'Yes' : 'No'; ?></td>
                                    <td class="text-end">
                                        <div class="admin-action-buttons">
                                            <a href="edit-product.php?id=<?php echo e($product['id']); ?>" class="btn btn-outline-success btn-sm">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="delete-product.php?id=<?php echo e($product['id']); ?>" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="admin-empty">
                    <i class="bi bi-basket"></i>
                    <h3 class="h5 fw-bold mt-3">No products yet</h3>
                    <p class="text-muted mb-4">Create your first farm product and upload its image.</p>
                    <a href="add-product.php" class="btn btn-success">Add Product</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
