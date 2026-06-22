<?php
require 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, skip straight to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];
$name = '';
$justRegistered = isset($_GET['registered']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $password === '') {
        $errors[] = "Please enter both name and password.";
    } else {
        // Look up the account by name
        $stmt = $pdo->prepare("SELECT * FROM users WHERE name = ? LIMIT 1");
        $stmt->execute([$name]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];

            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = "Invalid name or password.";
        }
    }
}

require 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h3 class="card-title mb-4 text-center">Login</h3>

                <?php if ($justRegistered && empty($errors)): ?>
                    <div class="alert alert-success">Registration successful! Please log in.</div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control"
                            value="<?= htmlspecialchars($name) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn w-100 text-white" style="background-color:#3730a3;">
                        Login
                    </button>
                </form>

                <p class="text-center mt-3 mb-0">
                    Don't have an account? <a href="reg.php">Register</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>