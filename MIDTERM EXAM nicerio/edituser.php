<?php
require_once 'core/auth.php';
requireAdmin();
require_once 'core/models.php';
require_once 'core/dbConfig.php';

$userId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
$user = $userId ? getManagedUserById($pdo, $userId) : false;
if (!$user) {
    http_response_code(404);
    exit('User account not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Account</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="reservation-page auth-page">
    <header class="site-header">
        <span class="site-brand">ANASTASIA E-SPORTS</span>
        <span class="current-tab">EDIT USER ACCOUNT</span>
    </header>
    <p class="page-links">
        <a href="admin.php">RESERVATION</a>
        <a href="customers.php">CUSTOMER RECORDS</a>
        <a href="finishedreservations.php">FINISHED RESERVATIONS</a>
        <a href="deletedreservations.php">DELETED RESERVATIONS</a>
        <a href="users.php">VIEW USER ACCOUNTS</a>
    </p>
    <?php require 'core/accountToolbar.php'; ?>
    <?php if (isset($_GET['error'])): ?>
        <p class="auth-error" role="alert">
            <?php
            echo match ($_GET['error']) {
                'taken' => 'That username or email is already in use.',
                default => 'Check the account details and password requirements.'
            };
            ?>
        </p>
    <?php endif; ?>
    <form action="core/handleForms.php" method="POST">
        <input type="hidden" name="user_id" value="<?php echo (int) $user['user_id']; ?>">
        <p>
            <label for="username">Username</label>
            <input id="username" type="text" name="username" value="<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>" minlength="3" maxlength="50" pattern="[A-Za-z0-9_.-]+" required>
        </p>
        <p>
            <label for="first_name">First name</label>
            <input id="first_name" type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="100" required>
        </p>
        <p>
            <label for="last_name">Last name</label>
            <input id="last_name" type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="100" required>
        </p>
        <p>
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="255" required>
        </p>
        <p>
            <label for="address">Address</label>
            <input id="address" type="text" name="address" value="<?php echo htmlspecialchars($user['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" maxlength="255">
        </p>
        <p>
            <label for="age">Age</label>
            <input id="age" type="number" name="age" min="1" max="120" value="<?php echo htmlspecialchars((string) ($user['age'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        </p>
        <p>
            <label for="phone">Phone</label>
            <input id="phone" type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" maxlength="30">
        </p>
        <p>
            <input type="submit" name="updateUserBtn" value="Update Account">
        </p>
    </form>
</body>
</html>
