<?php
// Dito nagpapalit ng sariling password; kailangan ang kasalukuyang password.
require_once 'core/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | ANASTASIA E-sports</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="reservation-page auth-page">
    <header class="site-header">
        <span class="site-brand">ANASTASIA E-SPORTS</span>
        <span class="current-tab">CHANGE PASSWORD</span>
    </header>
    <p class="page-links">
        <?php if (isAdmin()): ?>
            <a href="admin.php">RESERVATION</a>
            <a href="customers.php">CUSTOMER RECORDS</a>
            <a href="finishedreservations.php">FINISHED RESERVATIONS</a>
            <a href="deletedreservations.php">DELETED RESERVATIONS</a>
            <a href="users.php">VIEW USER ACCOUNTS</a>
        <?php else: ?>
            <a href="index.php">BACK TO RESERVATIONS</a>
            <a href="profile.php">ACCOUNT INFORMATION</a>
        <?php endif; ?>
    </p>
    <?php require 'core/accountToolbar.php'; ?>
    <p>Username: <?php echo htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?> (cannot be changed here)</p>
    <?php if (isset($_GET['updated'])): ?>
        <p class="reservation-success" role="status">Your password has been updated.</p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <p class="auth-error" role="alert">
            <?php
            echo match ($_GET['error']) {
                'current_password' => 'Current password is incorrect.',
                'confirm' => 'New password and confirmation do not match.',
                'password' => 'New password must be at least 4 characters long.',
                default => 'Unable to update password.'
            };
            ?>
        </p>
    <?php endif; ?>
    <form action="core/handleForms.php" method="POST">
        <p>
            <label for="current_password">Current password</label>
            <input id="current_password" type="password" name="current_password" autocomplete="current-password" required>
        </p>
        <p>
            <label for="new_password">New password</label>
            <input id="new_password" type="password" name="new_password" autocomplete="new-password" minlength="4" required>
        </p>
        <p>
            <label for="confirm_password">Confirm new password</label>
            <input id="confirm_password" type="password" name="confirm_password" autocomplete="new-password" minlength="4" required>
        </p>
        <p>
            <input type="submit" name="changeOwnPasswordBtn" value="Update Password">
        </p>
    </form>
</body>
</html>
