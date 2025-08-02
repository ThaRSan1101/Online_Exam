<?php
require '../config/db.php';
require '../includes/session.php';
if ($_SESSION['role'] !== 'admin') exit("Access denied");

session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Manage Users</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch users from the database
                $stmt = $conn->query("SELECT * FROM users ORDER BY id ASC");
                if($stmt->num_rows > 0) {
                        while ($user = $stmt->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                        echo "</tr>";
                    }
                }else{
                    echo "<tr><td colspan='4'>No users found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>