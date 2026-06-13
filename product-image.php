<?php
require_once 'includes/db.php';

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$productId) {
    http_response_code(404);
    exit;
}

$stmt = $pdo->prepare(
    'SELECT image_path, image_data, image_mime
     FROM products
     WHERE id = :id
     LIMIT 1'
);
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    exit;
}

if (!empty($product['image_data']) && !empty($product['image_mime'])) {
    $imageData = base64_decode($product['image_data'], true);

    if ($imageData !== false) {
        header('Content-Type: ' . $product['image_mime']);
        header('Cache-Control: public, max-age=86400');
        echo $imageData;
        exit;
    }
}

if (!empty($product['image_path'])) {
    $fullPath = __DIR__ . '/' . ltrim($product['image_path'], '/');

    if (is_file($fullPath)) {
        $mimeType = mime_content_type($fullPath) ?: 'image/jpeg';
        header('Content-Type: ' . $mimeType);
        header('Cache-Control: public, max-age=86400');
        readfile($fullPath);
        exit;
    }
}

http_response_code(404);
