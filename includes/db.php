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

// Database settings. Render free databases use PostgreSQL, while old local setups can still use MySQL.
$databaseUrl = getenv('DATABASE_URL') ?: '';
$driver = getenv('DB_DRIVER') ?: 'pgsql';
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: ($driver === 'mysql' ? '3307' : '5432');
$database = getenv('DB_NAME') ?: 'greenharvest_farm';
$username = getenv('DB_USER') ?: ($driver === 'mysql' ? 'root' : 'greenharvest_user');
$password = getenv('DB_PASS') ?: ($driver === 'mysql' ? '1234' : 'greenharvest_pass');
$sslMode = '';

if ($databaseUrl !== '') {
    $url = parse_url($databaseUrl);

    if ($url && isset($url['scheme']) && strpos($url['scheme'], 'postgres') === 0) {
        $driver = 'pgsql';
        $host = $url['host'] ?? $host;
        $port = $url['port'] ?? '5432';
        $database = isset($url['path']) ? ltrim($url['path'], '/') : $database;
        $username = isset($url['user']) ? rawurldecode($url['user']) : $username;
        $password = isset($url['pass']) ? rawurldecode($url['pass']) : $password;

        if (!empty($url['query'])) {
            parse_str($url['query'], $query);
            $sslMode = $query['sslmode'] ?? '';
        }
    }
}

if ($driver === 'mysql') {
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
} else {
    $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
    if ($sslMode !== '') {
        $dsn .= ";sslmode={$sslMode}";
    }
}

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

function databaseDriver()
{
    global $driver;
    return $driver;
}

function insertAndReturnId(PDO $pdo, $sql, array $params)
{
    if (databaseDriver() === 'pgsql') {
        $sql .= ' RETURNING id';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if (databaseDriver() === 'pgsql') {
        return (int) $stmt->fetchColumn();
    }

    return (int) $pdo->lastInsertId();
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
