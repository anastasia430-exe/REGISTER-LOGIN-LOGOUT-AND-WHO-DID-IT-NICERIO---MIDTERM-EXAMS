<?php
require_once 'core/auth.php';
requireAdmin();
require_once 'core/models.php';
require_once 'core/dbConfig.php';

$reservationId = filter_input(INPUT_GET, 'reservation_id', FILTER_VALIDATE_INT);
$reservation = $reservationId ? getReservationByID($pdo, $reservationId, currentUserId(), isAdmin()) : false;
if (!$reservation) {
    http_response_code(404);
    exit('Reservation not found.');
}
$isOpenHour = (int) $reservation['hours_to_play'] === 0;
$pcs = getAllPCs($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Reservation</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="reservation-page">
    <header class="site-header">
        <span class="site-brand">ANASTASIA E-SPORTS</span>
        <span class="current-tab">EDIT RESERVATION</span>
    </header>
    <p class="page-links">
        <a href="admin.php">RESERVATION</a>
        <a href="customers.php">CUSTOMER RECORDS</a>
        <a href="finishedreservations.php">FINISHED RESERVATIONS</a>
        <a href="deletedreservations.php">DELETED RESERVATIONS</a>
        <a href="users.php">VIEW USER ACCOUNTS</a>
    </p>
    <?php require 'core/accountToolbar.php'; ?>
    <form action="core/handleForms.php?reservation_id=<?php echo (int) $reservationId; ?>" method="POST">
        <p>
            <label>Customer Name</label>
            <input type="text" name="customerName" value="<?php echo htmlspecialchars($reservation['customer_name']); ?>" required>
        </p>
        <p>
            <label>Reservation Date</label>
            <input type="date" name="reservationDate" value="<?php echo htmlspecialchars($reservation['reservation_date']); ?>" required>
        </p>
        <p>
            <label>Play Type</label>
            <select id="playType" name="playType" required>
                <option value="Normal" <?php echo (($reservation['play_type'] ?? 'Normal') === 'Normal' ? 'selected' : ''); ?>>Regular</option>
                <option value="VIP" <?php echo (($reservation['play_type'] ?? '') === 'VIP') ? 'selected' : ''; ?>>VIP</option>
            </select>
        </p>
        <p>
            <label id="pcNumberLabel" for="pcID">Which PC?</label>
            <select id="pcID" name="pcID" data-current-pc="<?php echo (int) $reservation['pc_id']; ?>" required>
                <option value="">Choose a PC</option>
                <?php foreach ($pcs as $pc): ?>
                    <option value="<?php echo (int) $pc['pc_id']; ?>" data-pc-type="<?php echo htmlspecialchars($pc['pc_type']); ?>">
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
                        <option value="<?php echo $hours; ?>" <?php echo (int) $reservation['hours_to_play'] === $hours ? 'selected' : ''; ?>>
                            <?php echo $hours; ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <button type="button" class="open-hour-button" aria-pressed="<?php echo $isOpenHour ? 'true' : 'false'; ?>">Open Hour</button>
                <input type="hidden" name="hoursToPlay" value="0" disabled>
            </span>
        </p>
        <p class="notes-field">
            <label>Notes</label>
            <textarea name="notes" rows="5"><?php echo htmlspecialchars($reservation['notes'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </p>
        <p>
            <label for="status">Reservation status</label>
            <select id="status" name="status" required>
                <?php foreach (['Pending', 'Approved', 'Declined', 'Finished'] as $status): ?>
                    <option value="<?php echo $status; ?>" <?php echo $reservation['status'] === $status ? 'selected' : ''; ?>>
                        <?php echo $status; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <input type="submit" name="editReservationBtn" value="Save Reservation">
        </p>
    </form>
    <script>
        const playType = document.getElementById('playType');
        const pcSelect = document.getElementById('pcID');
        const pcNumberLabel = document.getElementById('pcNumberLabel');
        let selectedPc = pcSelect.dataset.currentPc;

        function updatePcChoices() {
            const pcType = playType.value === 'VIP' ? 'VIP' : 'Regular';
            pcNumberLabel.textContent = pcType === 'VIP' ? 'Which VIP PC? (20–40)' : 'Which Regular PC? (1–20)';
            Array.from(pcSelect.options).forEach((option) => {
                if (option.dataset.pcType) {
                    option.hidden = option.dataset.pcType !== pcType;
                    option.disabled = option.dataset.pcType !== pcType;
                }
            });

            const selectedOption = Array.from(pcSelect.options).find((option) => option.value === selectedPc);
            pcSelect.value = selectedOption && selectedOption.dataset.pcType === pcType ? selectedPc : '';
        }

        playType.addEventListener('change', () => {
            selectedPc = '';
            updatePcChoices();
        });
        pcSelect.addEventListener('change', () => {
            selectedPc = pcSelect.value;
        });
        updatePcChoices();

        document.querySelectorAll('.hours-field').forEach((field) => {
            const duration = field.querySelector('select[name="hoursToPlay"]');
            const openHourValue = field.querySelector('input[type="hidden"][name="hoursToPlay"]');
            const openHourButton = field.querySelector('.open-hour-button');
            const isOpenHour = openHourButton.getAttribute('aria-pressed') === 'true';

            duration.disabled = isOpenHour;
            duration.required = !isOpenHour;
            openHourValue.disabled = !isOpenHour;

            openHourButton.addEventListener('click', () => {
                const nextIsOpenHour = openHourButton.getAttribute('aria-pressed') !== 'true';
                openHourButton.setAttribute('aria-pressed', String(nextIsOpenHour));
                duration.disabled = nextIsOpenHour;
                duration.required = !nextIsOpenHour;
                openHourValue.disabled = !nextIsOpenHour;
            });
        });
    </script>
</body>
</html>
