<?php
require_once 'core/auth.php';
requireLogin();
require_once 'core/models.php';
require_once 'core/dbConfig.php';

$user = getUserById($pdo, currentUserId());
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
    <title>Account Information | ANASTASIA E-sports</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="reservation-page auth-page">
    <header class="site-header">
        <span class="site-brand">ANASTASIA E-SPORTS</span>
        <span class="current-tab">ACCOUNT INFORMATION</span>
    </header>
    <p class="page-links">
        <a href="index.php">BACK TO RESERVATIONS</a>
        <a href="account.php">CHANGE PASSWORD</a>
    </p>
    <?php require 'core/accountToolbar.php'; ?>
    <h1>Account Information</h1>
    <?php if (isset($_GET['updated'])): ?>
        <p class="reservation-success" role="status">Your account information has been updated.</p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <p class="auth-error" role="alert">
            <?php
            echo match ($_GET['error']) {
                'details' => 'Enter valid name and contact details.',
                'email_taken' => 'That email address is already in use.',
                default => 'Unable to update your account information.'
            };
            ?>
        </p>
    <?php endif; ?>
    <form action="core/handleForms.php" method="POST">
        <p>
            <label for="username">Username</label>
            <input id="username" type="text" value="<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>" disabled>
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
            <label for="phone">Phone</label>
            <input id="phone" type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" maxlength="30">
        </p>
        <p>
            <label for="address">Address</label>
            <input id="address" type="text" name="address" value="<?php echo htmlspecialchars($user['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" maxlength="255">
        </p>
        <p>
            <label for="age">Age</label>
            <input id="age" type="number" name="age" value="<?php echo htmlspecialchars((string) ($user['age'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" min="1" max="120">
        </p>
        <p>
            <input type="submit" name="updateOwnProfileBtn" value="Save Account Information">
        </p>
    </form>
</body>
</html>
