<?php
require_once 'includes/admin-auth.php';

$error = '';
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } elseif (hash_equals(getAdminUsername(), $login) && hash_equals(getAdminPassword(), $password)) {
        session_regenerate_id(true);
        unset($_SESSION['user_logged_in'], $_SESSION['user_id'], $_SESSION['user_name']);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $login;

        header('Location: admin/dashboard.php');
        exit;
    } else {
        $stmt = $pdo->prepare(
            'SELECT *
             FROM customer_accounts
             WHERE email = :login_email
             LIMIT 1'
        );
        $stmt->execute([
            'login_email' => $login,
        ]);
        $account = $stmt->fetch();

        if ($account && password_verify($password, $account['password_hash'])) {
            session_regenerate_id(true);
            unset($_SESSION['admin_logged_in'], $_SESSION['admin_username']);
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = (int) $account['id'];
            $_SESSION['user_name'] = $account['full_name'];

            header('Location: user-dashboard.php');
            exit;
        }

        $error = 'Invalid login details. Please try again or create an account.';
    }
}

$pageTitle = 'Login';
require_once 'includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-shell">
            <div class="auth-panel">
                <span class="hero-kicker"><i class="bi bi-shield-check"></i> Secure access</span>
                <h1>Welcome Back</h1>
                <p>Login to continue shopping, check your cart, and manage your customer profile.</p>
                <div class="auth-benefits">
                    <div><i class="bi bi-basket2"></i><span>Shop fresh farm products</span></div>
                    <div><i class="bi bi-bag-check"></i><span>Continue checkout faster</span></div>
                    <div><i class="bi bi-person-check"></i><span>Keep your customer profile</span></div>
                </div>
            </div>

            <div class="auth-card">
                <div class="auth-card-header">
                    <span class="brand-icon"><i class="bi bi-person-circle"></i></span>
                    <div>
                        <h2>Sign In</h2>
                        <p>Use your customer account to continue shopping.</p>
                    </div>
                </div>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><?php echo e($error); ?></div>
                <?php endif; ?>

                <form action="login.php" method="post">
                    <div class="mb-3">
                        <label for="login" class="form-label fw-bold">Email Address</label>
                        <input type="text" class="form-control" id="login" name="login" value="<?php echo e($login); ?>" placeholder="Enter your email address" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label fw-bold">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                </form>

                <div class="auth-divider"><span>or</span></div>

                <a href="guest.php" class="btn btn-outline-success w-100 auth-guest-button">
                    <i class="bi bi-arrow-right-circle me-2"></i>Continue without login
                </a>

                <div class="auth-switch">
                    <span>New customer?</span>
                    <a href="signup.php">Create an account</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
