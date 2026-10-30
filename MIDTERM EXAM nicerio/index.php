<?php
require_once 'core/auth.php';
requireLogin();
require_once 'core/models.php';
require_once 'core/dbConfig.php';

$reservations = getReservations($pdo, currentUserId(), isAdmin());
$pcs = getAllPCs($pdo);
$currentUser = getUserById($pdo, currentUserId());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ANASTASIA E-sports</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="reservation-page">

    <header class="site-header">
        <span class="site-brand">ANASTASIA E-SPORTS</span>
        <span class="current-tab"><?php echo isAdmin() ? 'RESERVATION' : 'RESERVATIONS'; ?></span>
    </header>
    <p class="page-links">
        <?php if (isAdmin()): ?>
            <a href="admin.php">RESERVATION</a>
            <a href="customers.php">CUSTOMER RECORDS</a>
            <a href="finishedreservations.php">FINISHED RESERVATIONS</a>
            <a href="deletedreservations.php">DELETED RESERVATIONS</a>
            <a href="users.php">VIEW USER ACCOUNTS</a>
        <?php else: ?>
            <a href="profile.php">ACCOUNT INFORMATION</a>
            <a href="account.php">CHANGE PASSWORD</a>
        <?php endif; ?>
    </p>
    <?php require 'core/accountToolbar.php'; ?>

   
    <h2>Reserve a Computer</h2>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_date'): ?>
        <p>Please choose a valid reservation date that is today or later.</p>
    <?php endif; ?>
    <form id="reservation-form" action="core/handleForms.php" method="POST">
        <?php if (isAdmin()): ?>
            <p>
                <label for="customerName">Customer Name</label>
                <input id="customerName" type="text" name="customerName" maxlength="100" required>
            </p>
        <?php else: ?>
            <div class="reservation-account-name">
                <span>Reservation will be made under your account name: <strong><?php echo htmlspecialchars(trim(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></strong></span>
                <button type="button" class="alternate-customer-button" aria-expanded="false" aria-controls="alternateCustomerField">
                    Reserve for someone else
                </button>
            </div>
            <p id="alternateCustomerField" class="alternate-customer-field" hidden>
                <label for="alternateCustomerName">Customer name</label>
                <input id="alternateCustomerName" type="text" name="alternateCustomerName" maxlength="100" disabled>
            </p>
        <?php endif; ?>
        <p>
            <label>Reservation Date</label>
            <input type="date" name="reservationDate" min="<?php echo date('Y-m-d'); ?>" required>
        </p>
        <p>
            <label>Play Type</label>
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
                    <option value="<?php echo (int) $pc['pc_id']; ?>" data-pc-type="<?php echo htmlspecialchars($pc['pc_type']); ?>" hidden>
                        <?php echo htmlspecialchars($pc['pc_type'] . ' PC ' . $pc['pc_number']); ?>
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
            <label>Notes</label>
            <textarea name="notes" rows="5"></textarea>
        </p>
        <p>
            <input type="submit" name="insertReservationBtn" value="Reserve Play">
        </p>
    </form>

    <?php if (!isAdmin()): ?>
        <h2>Your Reservations</h2>
        <table>
            <tr>
                <th>Reservation ID</th>
                <th>Customer</th>
                <th>Reservation Date</th>
                <th>Reserved At</th>
                <th>Status</th>
                <th>Play Type</th>
                <th>PC</th>
                <th>Hours</th>
                <th>Notes</th>
                <th>Action</th>
            </tr>
            <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td><?php echo (int) $reservation['reservation_id']; ?></td>
                    <td><?php echo htmlspecialchars($reservation['customer_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($reservation['reservation_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($reservation['date_added'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($reservation['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo ($reservation['play_type'] ?? 'Normal') === 'Normal' ? 'Regular' : htmlspecialchars($reservation['play_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo $reservation['reserved_pc_number'] === null ? 'Not selected' : htmlspecialchars($reservation['reserved_pc_type'] . ' PC ' . $reservation['reserved_pc_number'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php echo (int) ($reservation['hours_to_play'] ?? 1) === 0
                            ? 'Open Hour'
                            : htmlspecialchars($reservation['hours_to_play'] ?? '1', ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td><?php echo trim((string) ($reservation['notes'] ?? '')) !== ''
                        ? htmlspecialchars($reservation['notes'], ENT_QUOTES, 'UTF-8')
                        : 'N/A'; ?></td>
                    <td>
                        <?php if ($reservation['status'] === 'Pending'): ?>
                            <a href="deletedreservations.php?reservation_id=<?php echo (int) $reservation['reservation_id']; ?>">DELETE</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
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

        const alternateCustomerButton = document.querySelector('.alternate-customer-button');
        const alternateCustomerField = document.getElementById('alternateCustomerField');
        const alternateCustomerName = document.getElementById('alternateCustomerName');

        if (alternateCustomerButton && alternateCustomerField && alternateCustomerName) {
            alternateCustomerButton.addEventListener('click', () => {
                const useAlternateName = alternateCustomerButton.getAttribute('aria-expanded') !== 'true';
                alternateCustomerButton.setAttribute('aria-expanded', String(useAlternateName));
                alternateCustomerButton.textContent = useAlternateName
                    ? 'Use my account name'
                    : 'Reserve for someone else';
                alternateCustomerField.hidden = !useAlternateName;
                alternateCustomerName.disabled = !useAlternateName;
                alternateCustomerName.required = useAlternateName;

                if (!useAlternateName) {
                    alternateCustomerName.value = '';
                } else {
                    alternateCustomerName.focus();
                }
            });
        }
    </script>
    <?php if (isset($_GET['saved'])): ?>
        <p class="reservation-success" role="status">
            Reservation saved successfully for <?php echo htmlspecialchars($_GET['customer'] ?? 'the customer', ENT_QUOTES, 'UTF-8'); ?>.
        </p>
    <?php elseif (isset($_GET['updated'])): ?>
        <p class="reservation-success" role="status">Reservation is updated successfully.</p>
    <?php elseif (isset($_GET['deleted'])): ?>
        <p class="reservation-success" role="status">Reservation is deleted successfully.</p>
    <?php endif; ?>
</body>
</html>
