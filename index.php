<?php
session_start();
require 'config/db.php';
require_once 'classes/User.php';

$userObj = new User($conn);

// Handle Login 
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    // Find user by email
if ($userObj->findByEmail($email)) {
        // Verify user password
if ($userObj->verifyPassword($password)) {
            if ($userObj->status === 'disable') {
                $error = "Your account is disabled.";
            } else {
                $_SESSION['user_id'] = $userObj->id;
                $_SESSION['role'] = $userObj->role;
                if ($userObj->role === 'admin') {
                    header("Location: admin/manage_exams.php");
                } else {
                    header("Location: student/dashboard.php");
                }
                exit();
            }
        } else {
            $error = "Invalid login credentials.";
        }
    } else {
        $error = "Invalid login credentials.";
    }
}

// Handle Register 
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    // Register a new user
if ($userObj->register($name, $email, $password)) {
        $success = "Registered successfully! Please login.";
    } else {
        $error = "Registration failed. Email might already exist.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Online Exam - Login/Register</title>
    <link rel="stylesheet" href="css/styles.css?v=<?= time() ?>">
</head>

<body>
    <!-- <nav class="custom-navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <img src="images/logo.png" alt="Logo" class="logo">
                <span class="brand-text">ONLINE EXAMINATION SYSTEM</span>
            </div>
        </div>
    </nav> -->

    <div class="main-container">
        <div class="form-wrapper">
            <h3 class="form-title" id="form-title">Login</h3>

            <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <?php if (!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

            <!-- Login Form -->
            <form method="POST" id="login-form">
                <input type="hidden" name="login" value="1">
                <div class="form-group">
                    <label class="form-label">Email:</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password:</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                <button class="form-button primary">Login</button>
                <p class="form-footer">Don't have an account? <span class="toggle-link" onclick="showRegister()">Sign Up</span></p>
            </form>

            <!-- Register Form -->
            <form method="POST" id="register-form" class="hidden">
                <input type="hidden" name="register" value="1">
                <div class="form-group">
                    <label class="form-label">Name:</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email:</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password:</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                <button class="form-button success">Register</button>
                <p class="form-footer">Already have an account? <span class="toggle-link" onclick="showLogin()">Login</span></p>
            </form>
        </div>
    </div>

    <script src="js/index.js"></script>
</body>

</html>