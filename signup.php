<?php
require_once 'includes/db.php';

if (!empty($_SESSION['user_logged_in'])) {
    header('Location: user-dashboard.php');
    exit;
}

$errors = [];
$formData = [
    'full_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'city' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($formData as $field => $value) {
        $formData[$field] = trim($_POST[$field] ?? '');
    }

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($formData['full_name'] === '') {
        $errors[] = 'Full name is required.';
    }

    if ($formData['last_name'] === '') {
        $errors[] = 'Last name is required.';
    }

    if ($formData['email'] === '' || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    if ($formData['phone'] === '') {
        $errors[] = 'Phone number is required.';
    }

    if ($formData['address'] === '') {
        $errors[] = 'Address is required.';
    }

    if ($formData['city'] === '') {
        $errors[] = 'City is required.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $checkStmt = $pdo->prepare(
            'SELECT id
             FROM customer_accounts
             WHERE email = :email
             LIMIT 1'
        );
        $checkStmt->execute([
            'email' => $formData['email'],
        ]);

        if ($checkStmt->fetch()) {
            $errors[] = 'Email already exists.';
        }
    }

    if (empty($errors)) {
        $accountId = insertAndReturnId(
            $pdo,
            'INSERT INTO customer_accounts (full_name, last_name, email, phone, address, city, password_hash)
             VALUES (:full_name, :last_name, :email, :phone, :address, :city, :password_hash)',
            [
                'full_name' => $formData['full_name'],
                'last_name' => $formData['last_name'],
                'email' => $formData['email'],
                'phone' => $formData['phone'],
                'address' => $formData['address'],
                'city' => $formData['city'],
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]
        );

        session_regenerate_id(true);
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $accountId;
        $_SESSION['user_name'] = $formData['full_name'];

        header('Location: user-dashboard.php');
        exit;
    }
}

$pageTitle = 'Create Account';
require_once 'includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-shell auth-shell-wide">
            <div class="auth-panel">
                <span class="hero-kicker"><i class="bi bi-person-plus"></i> Customer account</span>
                <h1>Create Your Account</h1>
                <p>Save your customer information and use it when shopping fresh products from GreenHarvest Farm.</p>
                <div class="auth-benefits">
                    <div><i class="bi bi-person-vcard"></i><span>Store customer information</span></div>
                    <div><i class="bi bi-truck"></i><span>Prepare delivery details faster</span></div>
                    <div><i class="bi bi-leaf"></i><span>Shop organic farm products</span></div>
                </div>
            </div>

            <div class="auth-card">
                <div class="auth-card-header">
                    <span class="brand-icon"><i class="bi bi-person-plus"></i></span>
                    <div>
                        <h2>Sign Up</h2>
                        <p>Enter your customer details.</p>
                    </div>
                </div>

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

                <form action="signup.php" method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="full_name" class="form-label fw-bold">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo e($formData['full_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label fw-bold">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo e($formData['last_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo e($formData['email']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-bold">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo e($formData['phone']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="city" class="form-label fw-bold">City</label>
                            <input type="text" class="form-control" id="city" name="city" value="<?php echo e($formData['city']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="address" class="form-label fw-bold">Address</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?php echo e($formData['address']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label fw-bold">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label fw-bold">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 mt-4">
                        <i class="bi bi-person-plus me-2"></i>Create Account
                    </button>
                </form>

                <div class="auth-switch">
                    <span>Already have an account?</span>
                    <a href="login.php">Sign in</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
