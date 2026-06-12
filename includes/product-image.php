<?php

function uploadProductImage(array $file, array &$errors)
{
    if (empty($file['name']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ((int) $file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Image upload failed. Please choose another image.';
        return null;
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSize = 2 * 1024 * 1024;
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions, true)) {
        $errors[] = 'Only JPG, JPEG, PNG, and WEBP images are allowed.';
        return null;
    }

    if ((int) $file['size'] > $maxSize) {
        $errors[] = 'Product image must not be larger than 2MB.';
        return null;
    }

    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        $errors[] = 'The uploaded file is not a valid image.';
        return null;
    }

    $uploadDirectory = dirname(__DIR__) . '/uploads/products/';
    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0777, true);
    }

    $fileName = 'product_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = $uploadDirectory . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $errors[] = 'Could not save the uploaded image.';
        return null;
    }

    return 'uploads/products/' . $fileName;
}

function removeProductImage($imagePath)
{
    if (empty($imagePath)) {
        return;
    }

    $fullPath = dirname(__DIR__) . '/' . ltrim($imagePath, '/');
    $uploadsRoot = realpath(dirname(__DIR__) . '/uploads/products');
    $imageFullPath = realpath($fullPath);

    if ($uploadsRoot && $imageFullPath && strpos($imageFullPath, $uploadsRoot) === 0 && is_file($imageFullPath)) {
        unlink($imageFullPath);
    }
}
