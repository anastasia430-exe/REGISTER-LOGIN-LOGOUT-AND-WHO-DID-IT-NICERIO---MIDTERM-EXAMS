<?php
require_once 'core/auth.php';
requireAdmin();
require_once 'core/models.php';
require_once 'core/dbConfig.php';

$reservations = getFinishedReservations($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finished Reservations | ANASTASIA E-sports</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="reservation-page">
    <header class="site-header">
        <span class="site-brand">ANASTASIA E-SPORTS</span>
        <span class="current-tab">FINISHED RESERVATIONS</span>
    </header>
    <p class="page-links">
        <a href="admin.php">RESERVATION</a>
        <a href="customers.php">CUSTOMER RECORDS</a>
        <a href="deletedreservations.php">DELETED RESERVATIONS</a>
        <a href="users.php">VIEW USER ACCOUNTS</a>
    </p>
    <?php require 'core/accountToolbar.php'; ?>
    <h1>Finished Reservations</h1>
    <?php if (!$reservations): ?>
        <p>No finished reservations are recorded.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Reservation ID</th>
                <th>Customer</th>
                <th>Requested By</th>
                <th>Reservation Date</th>
                <th>Play Type</th>
                <th>PC</th>
                <th>Hours</th>
                <th>Notes</th>
                <th>Status</th>
                <th>Finished At</th>
                <th>Action</th>
            </tr>
            <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td><?php echo (int) $reservation['reservation_id']; ?></td>
                    <td><?php echo htmlspecialchars($reservation['customer_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(trim(($reservation['creator_first_name'] ?? '') . ' ' . ($reservation['creator_last_name'] ?? '')) ?: 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($reservation['reservation_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($reservation['play_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(($reservation['reserved_pc_type'] ?? '') . ' PC ' . ($reservation['reserved_pc_number'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo (int) $reservation['hours_to_play'] === 0 ? 'Open Hour' : (int) $reservation['hours_to_play']; ?></td>
                    <td><?php echo htmlspecialchars($reservation['notes'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>Finished</td>
                    <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($reservation['last_updated'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <a href="deletedreservations.php?reservation_id=<?php echo (int) $reservation['reservation_id']; ?>">DELETE</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
