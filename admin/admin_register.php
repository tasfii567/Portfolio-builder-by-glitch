<?php
session_start();
include "config.php";

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($name == "" || $email == "" || $password == "" || $confirm_password == "") {
        $error = "Please fill all required fields.";
    } elseif ($password != $confirm_password) {
        $error = "Password and confirm password do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $check_email = "SELECT * FROM admins WHERE email='$email'";
        $result = mysqli_query($con, $check_email);

        if (mysqli_num_rows($result) > 0) {
            $error = "This admin email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO admins (name, email, phone, password, role, status)
                    VALUES ('$name', '$email', '$phone', '$hashed_password', 'admin', 'active')";

            if (mysqli_query($con, $sql)) {
                $success = "Admin registration successful. You can login now.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Register - Portfolio Builder</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/admin_style.css" rel="stylesheet">
</head>

<body>

<div class="container">
    <div class="auth-box">

        <div class="text-center mb-4">
            <h2>Portfolio Builder</h2>
            <p class="text-muted">Admin Registration Panel</p>
        </div>

        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0">Create Admin Account</h4>
            </div>

            <div class="card-body p-4">

                <?php if ($error != "") { ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php } ?>

                <?php if ($success != "") { ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                    </div>
                <?php } ?>

                <form action="" method="POST" onsubmit="return validateAdminRegister()">

                    <div class="mb-3">
                        <label class="form-label">Admin Name</label>
                        <input type="text" name="name" id="name" class="form-control"
                               placeholder="Enter admin name">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Admin Email</label>
                        <input type="email" name="email" id="email" class="form-control"
                               placeholder="Enter admin email">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control"
                               placeholder="Enter phone number">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control"
                               placeholder="Enter password">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                               placeholder="Confirm password">
                    </div>

                    <button type="submit" name="register" class="btn btn-primary w-100">
                        Register Admin
                    </button>

                </form>

                <p class="text-center small-text mt-3">
                    Already have an admin account?
                    <a href="admin_login.php">Login here</a>
                </p>

            </div>
        </div>

    </div>
</div>

<script src="assets/js/admin_script.js"></script>
</body>
</html>