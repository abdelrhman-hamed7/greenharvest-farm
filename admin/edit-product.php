<?php
require_once '../includes/admin-auth.php';
require_once '../includes/product-image.php';
requireAdmin();

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$productId) {
    header('Location: products.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
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

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$errors = [];
$formData = [
    'category_id' => $product['category_id'],
    'name' => $product['name'],
    'description' => $product['description'],
    'price' => $product['price'],
    'stock' => $product['stock'],
    'status' => $product['status'],
];
$isFeatured = (int) $product['is_featured'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($formData as $field => $value) {
        $formData[$field] = trim($_POST[$field] ?? '');
    }

    $categoryId = filter_var($formData['category_id'], FILTER_VALIDATE_INT);
    $price = filter_var($formData['price'], FILTER_VALIDATE_FLOAT);
    $stock = filter_var($formData['stock'], FILTER_VALIDATE_INT);
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $status = in_array($formData['status'], ['active', 'inactive'], true) ? $formData['status'] : 'active';

    if (!$categoryId) {
        $errors[] = 'Please choose a category.';
    }

    if ($formData['name'] === '') {
        $errors[] = 'Product name is required.';
    }

    if ($formData['description'] === '') {
        $errors[] = 'Product description is required.';
    }

    if ($price === false || $price < 0) {
        $errors[] = 'Product price must be a valid positive number.';
    }

    if ($stock === false || $stock < 0) {
        $errors[] = 'Product stock must be zero or more.';
    }

    $newImagePath = uploadProductImage($_FILES['image'] ?? [], $errors);
    $imagePath = $newImagePath ?: $product['image_path'];

    if (empty($errors)) {
        $updateStmt = $pdo->prepare(
            'UPDATE products
             SET category_id = :category_id,
                 name = :name,
                 description = :description,
                 price = :price,
                 stock = :stock,
                 image_path = :image_path,
                 is_featured = :is_featured,
                 status = :status
             WHERE id = :id'
        );
        $updateStmt->execute([
            'category_id' => $categoryId,
            'name' => $formData['name'],
            'description' => $formData['description'],
            'price' => $price,
            'stock' => $stock,
            'image_path' => $imagePath,
            'is_featured' => $isFeatured,
            'status' => $status,
            'id' => $productId,
        ]);

        if ($newImagePath && !empty($product['image_path'])) {
            removeProductImage($product['image_path']);
        }

        $_SESSION['admin_message'] = [
            'type' => 'success',
            'text' => 'Product updated successfully.',
        ];

        header('Location: products.php');
        exit;
    }
}

$pageTitle = 'Edit Product';
require_once '../includes/header.php';
?>

<section class="admin-page-header">
    <div class="container">
        <span class="badge badge-soft mb-3">Product management</span>
        <h1 class="display-6 fw-bold mb-2">Edit Product</h1>
        <p class="text-muted mb-0">Update product details and replace the image if needed.</p>
    </div>
</section>

<section class="admin-shell">
    <div class="container">
        <div class="admin-form-card">
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

            <form action="edit-product.php?id=<?php echo e($productId); ?>" method="post" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-lg-4">
                        <div class="admin-current-image">
                            <?php if (!empty($product['image_path'])): ?>
                                <img src="../<?php echo e($product['image_path']); ?>" alt="<?php echo e($product['name']); ?>">
                            <?php else: ?>
                                <div>
                                    <i class="bi bi-image"></i>
                                    <span>No image uploaded</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-bold">Product Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo e($formData['name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="category_id" class="form-label fw-bold">Category</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Choose category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo e($category['id']); ?>" <?php echo (int) $formData['category_id'] === (int) $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo e($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="price" class="form-label fw-bold">Price (RWF)</label>
                                <input type="number" class="form-control" id="price" name="price" value="<?php echo e($formData['price']); ?>" min="0" step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label for="stock" class="form-label fw-bold">Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" value="<?php echo e($formData['stock']); ?>" min="0" required>
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label fw-bold">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="5" required><?php echo e($formData['description']); ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="image" class="form-label fw-bold">Replace Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
                                <div class="form-text">Leave empty to keep the current image.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label fw-bold">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo $formData['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $formData['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1" <?php echo $isFeatured === 1 ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-bold" for="is_featured">Show as featured product</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="admin-form-actions">
                    <a href="products.php" class="btn btn-outline-success">Cancel</a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2-circle me-1"></i> Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
