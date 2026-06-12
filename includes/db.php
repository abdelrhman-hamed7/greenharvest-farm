<?php
// Store sessions inside the project so the cart works even if the default PHP temp folder is restricted.
$sessionPath = dirname(__DIR__) . '/storage/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);

// Start the session once so the shopping cart can be stored in $_SESSION later.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database settings. The getenv() values will be useful when we run with Docker.
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3307';
$database = getenv('DB_NAME') ?: 'greenharvest_farm';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '1234';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // Keep the public error simple. The real error is useful only during development.
    die('Database connection failed. Please check your database settings.');
}

// Escape output before displaying database/user data in HTML.
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Display prices consistently across the website.
function formatPrice($amount)
{
    return 'RWF ' . number_format((float) $amount, 0);
}

// Show a small price unit note for products where the unit matters.
function priceUnitLabel($product)
{
    $category = strtolower($product['category_name'] ?? '');
    $name = strtolower($product['name'] ?? $product['product_name'] ?? '');

    if ($category === 'vegetables' || $category === 'fruits') {
        return 'price per kg';
    }

    if (strpos($name, 'honey') !== false) {
        return 'price per jar';
    }

    if (strpos($name, 'eggs') !== false) {
        return 'price per one piece';
    }

    if (strpos($name, 'beans') !== false || strpos($name, 'maize flour') !== false) {
        return 'price per kg';
    }

    if (strpos($name, 'gift basket') !== false) {
        return 'price per basket';
    }

    if ($category === 'coffee & tea') {
        return 'price per pack';
    }

    if ($category === 'dairy' || strpos($name, 'milk') !== false) {
        return 'price per liter';
    }

    return '';
}

function displayProductPrice($product)
{
    $price = formatPrice($product['price'] ?? 0);
    $unitLabel = priceUnitLabel($product);

    if ($unitLabel !== '') {
        $price .= '<small class="price-unit">' . e($unitLabel) . '</small>';
    }

    return $price;
}
