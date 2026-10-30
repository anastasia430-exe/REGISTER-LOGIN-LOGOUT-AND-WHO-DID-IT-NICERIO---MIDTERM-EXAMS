<?php
require_once 'core/auth.php';
requireAdmin();
require_once 'core/models.php';
require_once 'core/dbConfig.php';

$customers = getAllCustomers($pdo);
$deletedCustomers = getDeletedCustomers($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Records | ANASTASIA E-sports</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="reservation-page">
    <header class="site-header">
        <span class="site-brand">ANASTASIA E-SPORTS</span>
        <span class="current-tab">CUSTOMER RECORDS</span>
    </header>
    <p class="page-links">
        <a href="admin.php">RESERVATION</a>
        <a href="finishedreservations.php">FINISHED RESERVATIONS</a>
        <a href="users.php">VIEW USER ACCOUNTS</a>
        <a href="deletedreservations.php">DELETED RESERVATIONS</a>
    </p>
    <?php require 'core/accountToolbar.php'; ?>
    <?php if (isset($_GET['updated'])): ?>
        <p class="reservation-success" role="status">Customer record updated.</p>
    <?php elseif (isset($_GET['deleted'])): ?>
        <p class="reservation-success" role="status">Customer record deleted and audit details saved.</p>
    <?php elseif (isset($_GET['restored'])): ?>
        <p class="reservation-success" role="status">Customer record restored.</p>
    <?php endif; ?>
    <h2>ACTIVE CUSTOMER RECORDS</h2>
    <?php if (!$customers): ?>
        <p>No customer records have been created.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Customer ID</th>
                <th>Customer Name</th>
                <th>Phone Number</th>
                <th>Email Address</th>
                <th>Reservation Date(s)</th>
                <th>Added By</th>
                <th>Last Updated By</th>
                <th>Last Updated</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><?php echo (int) $customer['customer_id']; ?></td>
                    <td><?php echo htmlspecialchars($customer['customer_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($customer['phone'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($customer['email'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($customer['reservation_dates'] ?: 'No reservations', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($customer['added_by_username'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($customer['updated_by_username'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($customer['last_updated'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <a href="editcustomer.php?customer_id=<?php echo (int) $customer['customer_id']; ?>">EDIT</a>
                        <form class="inline-action-form" action="core/handleForms.php" method="POST">
                            <input type="hidden" name="customer_id" value="<?php echo (int) $customer['customer_id']; ?>">
                            <button type="submit" name="deleteCustomerBtn" value="1">DELETE</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    <h2>DELETED CUSTOMER RECORDS</h2>
    <?php if (!$deletedCustomers): ?>
        <p>No deleted customer records.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Customer ID</th>
                <th>Customer Name</th>
                <th>Phone Number</th>
                <th>Email Address</th>
                <th>Reservation Date(s)</th>
                <th>Deleted By</th>
                <th>Deleted At</th>
                <th>Action</th>
            </tr>
            <?php foreach ($deletedCustomers as $customer): ?>
                <tr>
                    <td><?php echo (int) $customer['customer_id']; ?></td>
                    <td><?php echo htmlspecialchars($customer['customer_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($customer['phone'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($customer['email'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($customer['reservation_dates'] ?: 'No reservations', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($customer['deleted_by_username'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($customer['deleted_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <form class="inline-action-form" action="core/handleForms.php" method="POST">
                            <input type="hidden" name="customer_id" value="<?php echo (int) $customer['customer_id']; ?>">
                            <button type="submit" name="restoreCustomerBtn" value="1">RESTORE</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
