<?php
// Registration page para sa mga hindi pa naka-sign in.
require_once 'core/auth.php';
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | ANASTASIA E-sports</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="reservation-page auth-page">
    <h1>ANASTASIA E-sports</h1>
    <h2>REGISTER</h2>
    <?php if (isset($_GET['error'])): ?>
        <p class="auth-error" role="alert">
            <?php
            echo match ($_GET['error']) {
                'email' => 'Enter a valid email address.',
                'account_taken' => 'That username or email is already in use.',
                'password' => 'Password must be at least 4 characters long.',
                'username' => 'Use 3–50 letters, numbers, dots, dashes, or underscores for your username.',
                'details' => 'Please complete the required details with valid values.',
                default => 'Unable to register. Please try again.'
            };
            ?>
        </p>
    <?php endif; ?>
    <form action="core/handleForms.php" method="POST">
        <p>
            <label for="username">Username</label>
            <input id="username" type="text" name="username" autocomplete="username" minlength="3" maxlength="50" pattern="[A-Za-z0-9_.-]+" required>
        </p>
        <p>
            <label for="first_name">First name</label>
            <input id="first_name" type="text" name="first_name" autocomplete="given-name" maxlength="100" required>
        </p>
        <p>
            <label for="last_name">Last name</label>
            <input id="last_name" type="text" name="last_name" autocomplete="family-name" maxlength="100" required>
        </p>
        <p>
            <label for="email">Email</label>
            <input id="email" type="email" name="email" autocomplete="email" maxlength="255" required>
        </p>
        <p>
            <label for="password">Password</label>
            <input id="password" type="password" name="password" autocomplete="new-password" minlength="4" required>
        </p>
        <p>
            <label for="address">Address</label>
            <input id="address" type="text" name="address" autocomplete="street-address" maxlength="255">
        </p>
        <p>
            <label for="age">Age</label>
            <input id="age" type="number" name="age" min="1" max="120">
        </p>
        <p>
            <label for="phone">Phone</label>
            <input id="phone" type="tel" name="phone" autocomplete="tel" maxlength="30">
        </p>
        <p>
            <input type="submit" name="registerBtn" value="Create Account">
        </p>
    </form>
    <p class="auth-switch">
        <span>Already registered?</span>
        <a href="login.php">LOGIN</a>
    </p>
</body>
</html>
