<?php
require '../config/db.php';
require '../includes/session.php';
if ($_SESSION['role'] !== 'admin') exit("Access denied");

// session_start();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../css/manage_users.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top mb-4">
        <div class="container-fluid" style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
            <a class="navbar-brand d-flex align-items-center" href="#" style="margin-right: 20px;">
                <img src="../images/logo.png" alt="Logo" style="height:40px; margin-right:10px;">
            </a>
            <ul class="navbar-nav d-flex flex-row align-items-center" style="gap: 10px; margin-bottom: 0;">
                <li class="nav-item">
                    <a class="nav-link active" href="manage_exams.php">Manage Exams</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="manage_users.php">Manage Users</a>
                </li>
            </ul>
            <a class="nav-link" href="../logout.php" style="margin-left:auto; color:#fff;">Logout</a>
        </div>
    </nav>
    <div class="container">
        <h2 class="mb-4">User Management</h2>

        <div class="card">
            <div class="card-body">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch users from the database
                        $stmt = $conn->query("SELECT * FROM users where role='student' ORDER BY id ASC");
                        if ($stmt->num_rows > 0) {
                            while ($user = $stmt->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($user['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' class='text-center text-muted'>No users found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        &copy; <?php echo date('Y'); ?> Online Exam System. All rights reserved.
    </footer>

</body>

</html>