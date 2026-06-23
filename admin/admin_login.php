<?php
session_start();
include "config.php";

$error = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = $_POST['password'];

    if ($email == "" || $password == "") {
        $error = "Please enter email and password.";
    } else {
        $sql = "SELECT * FROM admins WHERE email='$email' LIMIT 1";
        $result = mysqli_query($con, $sql);

        if (mysqli_num_rows($result) == 1) {
            $admin = mysqli_fetch_assoc($result);

            if ($admin['status'] != 'active') {
                $error = "Your admin account is inactive.";
            } elseif (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];

                header("Location: admin_dashboard.php");
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "Admin email not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - Portfolio Builder</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/admin_style.css" rel="stylesheet">
</head>

<body>

<div class="container">
    <div class="auth-box">

        <div class="text-center mb-4">
            <h2>Portfolio Builder</h2>
            <p class="text-muted">Admin Login Panel</p>
        </div>

        <div class="card shadow">
            <div class="card-header bg-dark text-white text-center">
                <h4 class="mb-0">Admin Login</h4>
            </div>

            <div class="card-body p-4">

                <?php if ($error != "") { ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php } ?>

                <form action="" method="POST" onsubmit="return validateAdminLogin()">

                    <div class="mb-3">
                        <label class="form-label">Admin Email</label>
                        <input type="email" name="email" id="email" class="form-control"
                               placeholder="Enter admin email">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control"
                               placeholder="Enter password">
                    </div>

                    <button type="submit" name="login" class="btn btn-dark w-100">
                        Login
                    </button>

                </form>

                <p class="text-center small-text mt-3">
                    Do not have an admin account?
                    <a href="admin_register.php">Register here</a>
                </p>

            </div>
        </div>

    </div>
</div>

<script src="assets/js/admin_script.js"></script>
</body>
</html>