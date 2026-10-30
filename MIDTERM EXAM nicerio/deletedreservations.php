<?php
require_once 'core/auth.php';
requireLogin();
require_once 'core/models.php';
require_once 'core/dbConfig.php';

$isDeleteConfirmation = isset($_GET['reservation_id']);
if ($isDeleteConfirmation) {
    $reservationId = filter_input(INPUT_GET, 'reservation_id', FILTER_VALIDATE_INT);
    $reservation = $reservationId
        ? getReservationByID($pdo, $reservationId, currentUserId(), isAdmin())
        : false;
    if (!$reservation) {
        http_response_code(404);
        exit('Reservation not found.');
    }
    if (!isAdmin() && $reservation['status'] !== 'Pending') {
        http_response_code(403);
        exit('You can delete only your own pending reservations.');
    }
} else {
    requireAdmin();
    $reservations = getDeletedReservations($pdo);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isDeleteConfirmation ? 'Delete Reservation' : 'Deleted Reservations'; ?> | ANASTASIA E-sports</title>
    <link rel="stylesheet" href="styles.css?v=delete-confirmation-1">
</head>
<body class="reservation-page<?php echo $isDeleteConfirmation ? ' delete-confirmation-page' : ''; ?>">
    <header class="site-header">
        <span class="site-brand">ANASTASIA E-SPORTS</span>
        <span class="current-tab"><?php echo $isDeleteConfirmation ? 'DELETE RESERVATION' : 'DELETED RESERVATIONS'; ?></span>
    </header>
    <?php if (isAdmin()): ?>
        <p class="page-links">
            <a href="admin.php">RESERVATION</a>
            <a href="customers.php">CUSTOMER RECORDS</a>
            <a href="finishedreservations.php">FINISHED RESERVATIONS</a>
            <a href="deletedreservations.php">DELETED RESERVATIONS</a>
            <a href="users.php">VIEW USER ACCOUNTS</a>
        </p>
    <?php else: ?>
        <p class="page-links"><a href="index.php">BACK TO RESERVATIONS</a></p>
    <?php endif; ?>
    <?php require 'core/accountToolbar.php'; ?>
    <?php if ($isDeleteConfirmation): ?>
        <h1>Are you sure you want to delete this reservation?</h1>
        <div class="container">
            <h2>Customer: <?php echo htmlspecialchars($reservation['customer_name'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <h2>Customer ID: <?php echo (int) $reservation['customer_id']; ?></h2>
            <h2>Reservation Date: <?php echo htmlspecialchars($reservation['reservation_date'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <h2>Play Type: <?php echo htmlspecialchars($reservation['play_type'] ?? 'Normal', ENT_QUOTES, 'UTF-8'); ?></h2>
            <h2>PC: <?php echo $reservation['reserved_pc_number'] === null ? 'Not selected' : htmlspecialchars($reservation['reserved_pc_type'] . ' PC ' . $reservation['reserved_pc_number'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <h2>Hours to Play: <?php echo (int) $reservation['hours_to_play'] === 0 ? 'Open Hour' : (int) $reservation['hours_to_play']; ?></h2>
            <h2>Status: <?php echo htmlspecialchars($reservation['status'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <form action="core/handleForms.php?reservation_id=<?php echo (int) $reservationId; ?>" method="POST">
                <input type="submit" name="deleteReservationBtn" value="Delete">
                <a class="delete-cancel-button" href="<?php echo isAdmin() ? 'admin.php' : 'index.php'; ?>">CANCEL</a>
            </form>
        </div>
    <?php else: ?>
        <h1>Deleted Reservation Audit</h1>
        <?php if (!$reservations): ?>
            <p>No deleted reservations are recorded.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Reservation ID</th>
                    <th>Customer</th>
                    <th>Reservation Date</th>
                    <th>Status</th>
                    <th>Originally Added By</th>
                    <th>Deleted By</th>
                    <th>Deleted At</th>
                </tr>
                <?php foreach ($reservations as $reservation): ?>
                    <tr>
                        <td><?php echo (int) $reservation['reservation_id']; ?></td>
                        <td><?php echo htmlspecialchars($reservation['customer_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($reservation['reservation_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($reservation['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($reservation['added_by_username'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($reservation['deleted_by_username'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($reservation['deleted_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
