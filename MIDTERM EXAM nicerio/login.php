<?php
//ddalhin sa homepage kapag nakalogin
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
    <title>Login | ANASTASIA E-sports</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="reservation-page auth-page">
    <h1>ANASTASIA E-sports</h1>
    <h2>LOGIN</h2>
    <?php if (isset($_GET['error'])): ?>
        <p class="auth-error" role="alert">
            <?php
            echo match ($_GET['error']) {
                'invalid' => 'Invalid username/email or password.',
                'required' => 'Please log in to continue.',
                default => 'Unable to log in. Please try again.'
            };
            ?>
        </p>
    <?php endif; ?>
    <?php if (isset($_GET['registered'])): ?>
        <p class="reservation-success" role="status">Registration complete. Please log in.</p>
    <?php endif; ?>
    <form action="core/handleForms.php" method="POST">
        <p>
            <label for="login">Username or email</label>
            <input id="login" type="text" name="login" autocomplete="username" required>
        </p>
        <p>
            <label for="password">Password</label>
            <input id="password" type="password" name="password" autocomplete="current-password" required>
        </p>
        <p>
            <input type="submit" name="loginBtn" value="Login">
        </p>
    </form>
    <p class="auth-switch">
        <span>Don't have an account?</span>
        <a href="register.php">REGISTER</a>
    </p>
</body>
</html>
