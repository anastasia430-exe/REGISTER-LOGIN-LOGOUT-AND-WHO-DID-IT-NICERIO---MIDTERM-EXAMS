<?php
require_once 'core/auth.php';
requireAdmin();
require_once 'core/models.php';
require_once 'core/dbConfig.php';

$customerId = filter_input(INPUT_GET, 'customer_id', FILTER_VALIDATE_INT);
$customer = $customerId ? getCustomerByID($pdo, $customerId) : false;
if (!$customer) {
    http_response_code(404);
    exit('Customer record not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer | ANASTASIA E-sports</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="reservation-page auth-page">
    <header class="site-header">
        <span class="site-brand">ANASTASIA E-SPORTS</span>
        <span class="current-tab">EDIT CUSTOMER</span>
    </header>
    <p class="page-links">
        <a href="admin.php">RESERVATION</a>
        <a href="customers.php">CUSTOMER RECORDS</a>
        <a href="finishedreservations.php">FINISHED RESERVATIONS</a>
        <a href="users.php">VIEW USER ACCOUNTS</a>
        <a href="deletedreservations.php">DELETED RESERVATIONS</a>
    </p>
    <?php require 'core/accountToolbar.php'; ?>
    <?php if (isset($_GET['error'])): ?>
        <p class="auth-error" role="alert">
            <?php
            echo match ($_GET['error']) {
                'taken' => 'A customer with that name already exists.',
                'details' => 'Enter a customer name and valid contact details.',
                default => 'Unable to update the customer record.'
            };
            ?>
        </p>
    <?php endif; ?>
    <form action="core/handleForms.php" method="POST">
        <input type="hidden" name="customer_id" value="<?php echo (int) $customer['customer_id']; ?>">
        <p>
            <label for="customer_name">Customer name</label>
            <input id="customer_name" type="text" name="customer_name" value="<?php echo htmlspecialchars($customer['customer_name'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="100" required>
        </p>
        <p>
            <label for="phone">Phone number</label>
            <input id="phone" type="tel" name="phone" value="<?php echo htmlspecialchars($customer['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" maxlength="30">
        </p>
        <p>
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($customer['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" maxlength="255">
        </p>
        <p>
            <input type="submit" name="updateCustomerBtn" value="Update Customer">
        </p>
    </form>
</body>
</html>
