<?php
require_once 'core/auth.php';
requireAdmin();
require_once 'core/models.php';
require_once 'core/dbConfig.php';

// pinapakita lahattt ng reserve
$reservations = getReservations($pdo, currentUserId(), true);
$pcs = getAllPCs($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation | ANASTASIA E-sports</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="reservation-page">
    <header class="site-header">
        <span class="site-brand">ANASTASIA E-SPORTS</span>
        <span class="current-tab">RESERVATION</span>
    </header>
    <p class="page-links">
        <a href="customers.php">CUSTOMER RECORDS</a>
        <a href="finishedreservations.php">FINISHED RESERVATIONS</a>
        <a href="deletedreservations.php">DELETED RESERVATIONS</a>
        <a href="users.php">VIEW USER ACCOUNTS</a>
    </p>
    <?php require 'core/accountToolbar.php'; ?>

    <?php if (isset($_GET['decision'])): ?>
        <p class="reservation-success" role="status">
            Reservation <?php echo htmlspecialchars($_GET['decision']); ?>.
        </p>
    <?php elseif (isset($_GET['saved'])): ?>
        <p class="reservation-success" role="status">
            Reservation saved successfully for <?php echo htmlspecialchars($_GET['customer'] ?? 'the customer', ENT_QUOTES, 'UTF-8'); ?>.
        </p>
    <?php elseif (isset($_GET['updated'])): ?>
        <p class="reservation-success" role="status">Reservation updated successfully.</p>
    <?php elseif (isset($_GET['deleted'])): ?>
        <p class="reservation-success" role="status">Reservation deleted successfully.</p>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_date'): ?>
        <p class="auth-error" role="alert">Please choose a valid reservation date that is today or later.</p>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] !== 'invalid_date'): ?>
        <p class="auth-error" role="alert">
            Unable to complete the reservation action.
        </p>
    <?php endif; ?>

  
    <h2>ALL RESERVATIONS</h2>
    <?php if (!$reservations): ?>
        <p>No reservations have been submitted.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Reservation</th>
                <th>Requested By</th>
                <th>Customer</th>
                <th>Reservation Date</th>
                <th>Play Type</th>
                <th>PC</th>
                <th>Hours</th>
                <th>Notes</th>
                <th>Requested At</th>
                <th>Last Updated By</th>
                <th>Last Updated</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td><?php echo (int) $reservation['reservation_id']; ?></td>
                    <td><?php echo htmlspecialchars(trim(($reservation['creator_first_name'] ?? '') . ' ' . ($reservation['creator_last_name'] ?? '')) ?: 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($reservation['customer_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($reservation['reservation_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($reservation['play_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(($reservation['reserved_pc_type'] ?? '') . ' PC ' . ($reservation['reserved_pc_number'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo (int) $reservation['hours_to_play'] === 0 ? 'Open Hour' : (int) $reservation['hours_to_play']; ?></td>
                    <td><?php echo htmlspecialchars($reservation['notes'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($reservation['date_added'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(trim(($reservation['updater_first_name'] ?? '') . ' ' . ($reservation['updater_last_name'] ?? '')) ?: 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($reservation['last_updated'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($reservation['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php if ($reservation['status'] === 'Pending'): ?>
                            <form action="core/handleForms.php" method="POST">
                                <input type="hidden" name="reservation_id" value="<?php echo (int) $reservation['reservation_id']; ?>">
                                <input type="hidden" name="reservationDecisionBtn" value="1">
                                <button type="submit" name="decision" value="Approved">Approve</button>
                                <button type="submit" name="decision" value="Declined">Decline</button>
                            </form>
                        <?php elseif ($reservation['status'] === 'Approved'): ?>
                            <form action="core/handleForms.php" method="POST">
                                <input type="hidden" name="reservation_id" value="<?php echo (int) $reservation['reservation_id']; ?>">
                                <input type="hidden" name="reservationDecisionBtn" value="1">
                                <button type="submit" name="decision" value="Finished">Mark Finished</button>
                            </form>
                        <?php endif; ?>
                        <a href="editreservation.php?reservation_id=<?php echo (int) $reservation['reservation_id']; ?>">EDIT</a>
                        <a href="deletedreservations.php?reservation_id=<?php echo (int) $reservation['reservation_id']; ?>">DELETE</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <h2>RESERVE A COMPUTER</h2>
    <form id="reservation-form" action="core/handleForms.php" method="POST">
        <p>
            <label for="customerName">Customer Name</label>
            <input id="customerName" type="text" name="customerName" maxlength="100" required>
        </p>
        <p>
            <label for="reservationDate">Reservation Date</label>
            <input id="reservationDate" type="date" name="reservationDate" min="<?php echo date('Y-m-d'); ?>" required>
        </p>
        <p>
            <label for="playType">Play Type</label>
            <select id="playType" name="playType" required>
                <option value="Normal">Regular</option>
                <option value="VIP">VIP</option>
            </select>
        </p>
        <p>
            <label id="pcNumberLabel" for="pcID">Which PC? (1–20)</label>
            <select id="pcID" name="pcID" required>
                <option value="">Choose a PC</option>
                <?php foreach ($pcs as $pc): ?>
                    <option value="<?php echo (int) $pc['pc_id']; ?>" data-pc-type="<?php echo htmlspecialchars($pc['pc_type'], ENT_QUOTES, 'UTF-8'); ?>" hidden>
                        <?php echo htmlspecialchars($pc['pc_type'] . ' PC ' . $pc['pc_number'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p class="hours-field">
            <label for="hoursToPlay">Hours</label>
            <span class="hours-control">
                <select id="hoursToPlay" name="hoursToPlay" required>
                    <?php for ($hours = 1; $hours <= 10; $hours++): ?>
                        <option value="<?php echo $hours; ?>"><?php echo $hours; ?></option>
                    <?php endfor; ?>
                </select>
                <button type="button" class="open-hour-button" aria-pressed="false">Open Hour</button>
                <input type="hidden" name="hoursToPlay" value="0" disabled>
            </span>
        </p>
        <p class="notes-field">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="5"></textarea>
        </p>
        <p>
            <input type="submit" name="insertReservationBtn" value="Reserve Play">
        </p>
    </form>
    <script>
        const playType = document.getElementById('playType');
        const pcSelect = document.getElementById('pcID');
        const pcNumberLabel = document.getElementById('pcNumberLabel');

        function updatePcChoices() {
            const pcType = playType.value === 'VIP' ? 'VIP' : 'Regular';
            pcNumberLabel.textContent = pcType === 'VIP' ? 'Which VIP PC? (20–40)' : 'Which Regular PC? (1–20)';
            pcSelect.value = '';
            Array.from(pcSelect.options).forEach((option) => {
                if (option.dataset.pcType) {
                    option.hidden = option.dataset.pcType !== pcType;
                    option.disabled = option.dataset.pcType !== pcType;
                }
            });
        }

        playType.addEventListener('change', updatePcChoices);
        updatePcChoices();

        document.querySelectorAll('.hours-field').forEach((field) => {
            const duration = field.querySelector('select[name="hoursToPlay"]');
            const openHourValue = field.querySelector('input[type="hidden"][name="hoursToPlay"]');
            const openHourButton = field.querySelector('.open-hour-button');

            openHourButton.addEventListener('click', () => {
                const isOpenHour = openHourButton.getAttribute('aria-pressed') !== 'true';
                openHourButton.setAttribute('aria-pressed', String(isOpenHour));
                duration.disabled = isOpenHour;
                duration.required = !isOpenHour;
                openHourValue.disabled = !isOpenHour;
            });
        });
    </script>
</body>
</html>
