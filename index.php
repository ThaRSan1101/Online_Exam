<?php
session_start();
require 'config/db.php';

// Handle Login
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        if ($user['role'] === 'admin') {
            header("Location: admin/manage_exams.php");
        } else {
            header("Location: student/dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid login credentials.";
    }
}

// Handle Register
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'student';

    $stmt = $conn->prepare("INSERT INTO users(name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);
    if ($stmt->execute()) {
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-wrapper {
            max-width: 500px;
            margin: auto;
            margin-top: 80px;
        }
        .toggle-link {
            color: blue;
            cursor: pointer;
        }
        .hidden { display: none; }
    </style>
</head>
<body class="bg-light">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <div class="mx-auto">
            <span class="navbar-brand h1 mb-0">ABINATH INSTITUTE</span>
        </div>
    </div>
</nav>

<div class="container">
    <div class="form-wrapper bg-white p-4 rounded shadow">
        <h3 class="text-center mb-3" id="form-title">Login</h3>

        <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <?php if (!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

        <!-- Login Form -->
        <form method="POST" id="login-form">
            <input type="hidden" name="login" value="1">
            <div class="mb-3">
                <label class="form-label">Email:</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100">Login</button>
            <p class="mt-3 text-center">Don't have an account? <span class="toggle-link" onclick="showRegister()">Sign Up</span></p>
        </form>

        <!-- Register Form -->
        <form method="POST" id="register-form" class="hidden">
    <input type="hidden" name="register" value="1">
    <div class="mb-3">
        <label class="form-label">Name:</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Email:</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Password:</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <button class="btn btn-success w-100">Register</button>
    <p class="mt-3 text-center">Already have an account? <span class="toggle-link" onclick="showLogin()">Login</span></p>
</form>
    </div>
</div>

<script>
function showRegister() {
    document.getElementById('login-form').classList.add('hidden');
    document.getElementById('register-form').classList.remove('hidden');
    document.getElementById('form-title').innerText = "Register";
}
function showLogin() {
    document.getElementById('register-form').classList.add('hidden');
    document.getElementById('login-form').classList.remove('hidden');
    document.getElementById('form-title').innerText = "Login";
}
</script>
</body>
</html>
