<?php
require_once 'Models/db.php';

$msg = '';

// Handle Actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $uid = intval($_POST['user_id'] ?? 0);

    if ($action === 'set_role' && $uid > 0) {
        $new_role = $_POST['role'] ?? 'customer';
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $new_role, $uid);
        if ($stmt->execute()) {
            $msg = "Updated user #$uid to role '$new_role'.";
        } else {
            $msg = "Error: " . $conn->error;
        }
    } elseif ($action === 'fix_nulls') {
        $conn->query("UPDATE users SET role = 'customer' WHERE role IS NULL OR role = ''");
        $msg = "Updated all users with missing roles to 'customer'.";
    } elseif ($action === 'migrate_notifications') {
        $check = $conn->query("SHOW COLUMNS FROM notifications LIKE 'user_id'");
        if ($check && $check->num_rows == 0) {
            $conn->query("ALTER TABLE notifications ADD COLUMN user_id INT DEFAULT NULL AFTER id");
            $conn->query("ALTER TABLE notifications ADD INDEX (user_id)");
            $msg = "Added user_id column to notifications table.";
        } else {
            $msg = "user_id column already exists.";
        }
    }
}

// Fetch Users
$users = [];
$res = $conn->query("SELECT * FROM users ORDER BY id DESC");
while ($row = $res->fetch_assoc()) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Role Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container bg-white p-4 rounded shadow">
        <h2 class="mb-4">User Role Management Tool</h2>
        
        <?php if ($msg): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div class="mb-4">
            <form method="post" class="d-inline">
                <input type="hidden" name="action" value="fix_nulls">
                <button type="submit" class="btn btn-warning">Fix All Missing Roles (Set to 'customer')</button>
            </form>
            <a href="Views/Users/index.php" class="btn btn-secondary ms-2">Back to Home</a>
        </div>

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Current Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['full_name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : ($u['role'] === 'customer' ? 'bg-primary' : 'bg-secondary') ?>">
                                <?= htmlspecialchars($u['role'] ?? 'NULL') ?>
                            </span>
                        </td>
                        <td>
                            <form method="post" class="d-flex gap-2">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="action" value="set_role">
                                <?php if ($u['role'] !== 'admin'): ?>
                                    <button type="submit" name="role" value="admin" class="btn btn-sm btn-outline-danger">Make Admin</button>
                                <?php endif; ?>
                                <?php if ($u['role'] !== 'customer'): ?>
                                    <button type="submit" name="role" value="customer" class="btn btn-sm btn-outline-primary">Make Customer</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
