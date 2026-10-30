<?php
require_once 'core/auth.php';
requireAdmin();
require_once 'core/models.php';
require_once 'core/dbConfig.php';

$users = getAllUsers($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | ANASTASIA E-sports</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="reservation-page">
    <header class="site-header">
        <span class="site-brand">ANASTASIA E-SPORTS</span>
        <span class="current-tab">VIEW USER ACCOUNTS</span>
    </header>
    <p class="page-links">
        <a href="admin.php">RESERVATION</a>
        <a href="customers.php">CUSTOMER RECORDS</a>
        <a href="finishedreservations.php">FINISHED RESERVATIONS</a>
        <a href="deletedreservations.php">DELETED RESERVATIONS</a>
    </p>
    <?php require 'core/accountToolbar.php'; ?>
    <?php if (isset($_GET['updated'])): ?>
        <p class="reservation-success" role="status">User account updated.</p>
    <?php endif; ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Name</th>
            <th>Email</th>
            <th>Address</th>
            <th>Age</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Registered</th>
            <th>Action</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo (int) $user['user_id']; ?></td>
                <td><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($user['address'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo $user['age'] === null ? '—' : (int) $user['age']; ?></td>
                <td><?php echo htmlspecialchars($user['phone'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($user['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                <td><a href="edituser.php?user_id=<?php echo (int) $user['user_id']; ?>">EDIT</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
