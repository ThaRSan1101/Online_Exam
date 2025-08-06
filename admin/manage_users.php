<?php
require '../config/db.php';
require '../includes/session.php';
require_once '../classes/User.php';
if ($_SESSION['role'] !== 'admin') exit("Access denied");

$userObj = new User($conn);
// Handle status update via form submit 
$status_msg = '';
if (isset($_POST['update_status'])) {
    $user_id = intval($_POST['user_id']);
    $status = $_POST['status'] === 'disable' ? 'disable' : 'active';
    // Update user status
    if ($userObj->updateStatus($user_id, $status)) {
        $status_msg = "<div class='alert alert-success'>Status updated successfully.</div>";
    } else {
        $status_msg = "<div class='alert alert-danger'>Failed to update status.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../css/manage_users.css">
    <link rel="stylesheet" href="../css/footer.css?v=<?= time() ?>">
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include '../components/admin_navbar.php'; ?>
    <div class="container">
        <h2 class="mb-4">User Management</h2>
        <?php if (!empty($status_msg)) echo $status_msg; ?>

        <div class="card">
            <div class="card-body">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get all student users
$students = $userObj->getAllStudents();
                        if (count($students) > 0) {
                            foreach ($students as $user) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($user['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($user['email']) . "</td>";

                                // Status dropdown and update form
                                $status = htmlspecialchars($user['status']);
                                $id = (int)$user['id'];
                                echo "<td>"
                                    . "<form method='POST' style='margin:0;'>"
                                    . "<input type='hidden' name='user_id' value='$id'>"
                                    . "<select name='status' onchange='this.form.submit()'>"
                                    . "<option value='active'" . ($status === 'active' ? ' selected' : '') . ">Active</option>"
                                    . "<option value='disable'" . ($status === 'disable' ? ' selected' : '') . ">Disable</option>"
                                    . "</select>"
                                    . "<input type='hidden' name='update_status' value='1'>"
                                    . "</form>"
                                    . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center text-muted'>No users found</td></tr>";
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
<script>
    document.getElementById('logout-link').addEventListener('click', function(e) {
        e.preventDefault();
        alert('You have been logged out!');
        window.location.href = '../logout.php';
    });
</script>
</html>