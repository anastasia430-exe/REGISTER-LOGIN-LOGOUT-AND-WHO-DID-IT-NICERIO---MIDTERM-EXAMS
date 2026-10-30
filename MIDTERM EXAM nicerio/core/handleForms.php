<?php

require_once 'dbConfig.php';
require_once 'models.php';
require_once 'auth.php';

if (isset($_POST['loginBtn'])) {
    ensureDefaultAdmin($pdo);
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = getUserByLogin($pdo, $login);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        header('Location: ../login.php?error=invalid');
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
    header('Location: ../index.php');
    exit;
}

// tinitignan ung registration form tas sinasave sa register
if (isset($_POST['registerBtn'])) {
    ensureDefaultAdmin($pdo);
    $username = trim($_POST['username'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $ageInput = trim($_POST['age'] ?? '');
    $age = $ageInput === '' ? null : filter_var($ageInput, FILTER_VALIDATE_INT);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ../register.php?error=email');
        exit;
    }
    if (strlen($password) < 4) {
        header('Location: ../register.php?error=password');
        exit;
    }
    if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
        header('Location: ../register.php?error=username');
        exit;
    }
    if (
        $firstName === '' || mb_strlen($firstName) > 100 ||
        $lastName === '' || mb_strlen($lastName) > 100 ||
        mb_strlen($email) > 255 || mb_strlen($address) > 255 || mb_strlen($phone) > 30 ||
        ($ageInput !== '' && ($age === false || $age < 1 || $age > 120))
    ) {
        header('Location: ../register.php?error=details');
        exit;
    }

    try {
        createUser($pdo, $username, $firstName, $lastName, $email, password_hash($password, PASSWORD_DEFAULT), $address, $age, $phone);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            header('Location: ../register.php?error=account_taken');
            exit;
        }
        throw $e;
    }

    header('Location: ../login.php?registered=1');
    exit;
}

requireLogin();

if (isset($_POST['updateOwnProfileBtn'])) {
    $userId = currentUserId();
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $ageInput = trim($_POST['age'] ?? '');
    $age = $ageInput === '' ? null : filter_var($ageInput, FILTER_VALIDATE_INT);

    if (
        $firstName === '' || mb_strlen($firstName) > 100 ||
        $lastName === '' || mb_strlen($lastName) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255 ||
        mb_strlen($address) > 255 || mb_strlen($phone) > 30 ||
        ($ageInput !== '' && ($age === false || $age < 1 || $age > 120))
    ) {
        header('Location: ../profile.php?error=details');
        exit;
    }

    try {
        updateOwnUserProfile($pdo, $userId, $firstName, $lastName, $email, $address, $age, $phone);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            header('Location: ../profile.php?error=email_taken');
            exit;
        }
        throw $e;
    }

    header('Location: ../profile.php?updated=1');
    exit;
}

if (isset($_POST['updateUserBtn'])) {
    requireAdmin();
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $username = trim($_POST['username'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $ageInput = trim($_POST['age'] ?? '');
    $age = $ageInput === '' ? null : filter_var($ageInput, FILTER_VALIDATE_INT);

    $managedUser = $userId ? getManagedUserById($pdo, $userId) : false;
    if (!$managedUser) {
        http_response_code(404);
        exit('User account not found.');
    }
    if (
        !preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username) ||
        $firstName === '' || mb_strlen($firstName) > 100 ||
        $lastName === '' || mb_strlen($lastName) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255 ||
        mb_strlen($address) > 255 ||
        mb_strlen($phone) > 30 ||
        ($ageInput !== '' && ($age === false || $age < 1 || $age > 120))
    ) {
        header('Location: ../edituser.php?user_id=' . $userId . '&error=details');
        exit;
    }

    try {
        updateManagedUser(
            $pdo,
            $userId,
            $username,
            $firstName,
            $lastName,
            $email,
            $address,
            $age,
            $phone
        );
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            header('Location: ../edituser.php?user_id=' . $userId . '&error=taken');
            exit;
        }
        throw $e;
    }

    if ($userId === currentUserId()) {
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = $managedUser['role'];
    }
    header('Location: ../users.php?updated=1');
    exit;
}

if (isset($_POST['updateCustomerBtn'])) {
    requireAdmin();
    $customerId = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
    $customerName = trim($_POST['customer_name'] ?? '');
    $customerPhone = trim($_POST['phone'] ?? '');
    $customerEmail = trim($_POST['email'] ?? '');

    if (!$customerId || !getCustomerByID($pdo, $customerId)) {
        http_response_code(404);
        exit('Customer record not found.');
    }
    if (
        $customerName === '' || mb_strlen($customerName) > 100 ||
        mb_strlen($customerPhone) > 30 ||
        mb_strlen($customerEmail) > 255 ||
        ($customerEmail !== '' && !filter_var($customerEmail, FILTER_VALIDATE_EMAIL))
    ) {
        header('Location: ../editcustomer.php?customer_id=' . $customerId . '&error=details');
        exit;
    }

    try {
        updateCustomer($pdo, $customerId, $customerName, $customerPhone, $customerEmail, currentUserId());
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            header('Location: ../editcustomer.php?customer_id=' . $customerId . '&error=taken');
            exit;
        }
        throw $e;
    }

    header('Location: ../customers.php?updated=1');
    exit;
}

if (isset($_POST['deleteCustomerBtn'])) {
    requireAdmin();
    $customerId = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
    if (!$customerId || !getCustomerByID($pdo, $customerId)) {
        http_response_code(404);
        exit('Customer record not found.');
    }

    if (!deleteCustomer($pdo, $customerId, currentUserId())) {
        http_response_code(409);
        exit('Customer record is already deleted or could not be deleted.');
    }

    header('Location: ../customers.php?deleted=1');
    exit;
}

if (isset($_POST['restoreCustomerBtn'])) {
    requireAdmin();
    $customerId = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
    if (!$customerId || !restoreCustomer($pdo, $customerId, currentUserId())) {
        http_response_code(404);
        exit('Deleted customer record not found.');
    }

    header('Location: ../customers.php?restored=1');
    exit;
}

// tinitignan unn gpassword
if (isset($_POST['changeOwnPasswordBtn'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $user = getUserById($pdo, currentUserId());

    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        header('Location: ../account.php?error=current_password');
        exit;
    }
    if (strlen($newPassword) < 4) {
        header('Location: ../account.php?error=password');
        exit;
    }
    if ($newPassword !== $confirmPassword) {
        header('Location: ../account.php?error=confirm');
        exit;
    }

    updateUserPassword($pdo, currentUserId(), password_hash($newPassword, PASSWORD_DEFAULT));
    header('Location: ../account.php?updated=1');
    exit;
}

// adminlang pwede mg approve sa reservvation 
if (isset($_POST['reservationDecisionBtn'])) {
    requireAdmin();
    $reservationId = filter_input(INPUT_POST, 'reservation_id', FILTER_VALIDATE_INT);
    $decision = $_POST['decision'] ?? '';
    if (!$reservationId || !in_array($decision, ['Approved', 'Declined', 'Finished'], true)) {
        http_response_code(400);
        exit('Invalid reservation decision.');
    }
    if (!setReservationStatus($pdo, $reservationId, $decision, currentUserId())) {
        http_response_code(409);
        exit('This reservation cannot be moved to that status.');
    }
    header('Location: ../admin.php?decision=' . strtolower($decision));
    exit;
}

//  nagsasabe ng reservatioon request na pending
if (isset($_POST['insertReservationBtn'])) {
    $reservationDate = $_POST['reservationDate'] ?? '';
    $dateValue = DateTimeImmutable::createFromFormat('!Y-m-d', $reservationDate);
    if (!$dateValue || $dateValue->format('Y-m-d') !== $reservationDate || $dateValue < new DateTimeImmutable('today')) {
        header('Location: ../' . (isAdmin() ? 'admin.php' : 'index.php') . '?error=invalid_date#reservation-form');
        exit;
    }

    if (isAdmin()) {
        $customerName = trim($_POST['customerName'] ?? '');
    } else {
        $user = getUserById($pdo, currentUserId());
        $alternateCustomerName = trim($_POST['alternateCustomerName'] ?? '');
        $customerName = $alternateCustomerName !== ''
            ? $alternateCustomerName
            : ($user ? trim($user['first_name'] . ' ' . $user['last_name']) : '');
    }

    if ($customerName === '' || mb_strlen($customerName) > 100) {
        $usingAlternateName = !isAdmin() && $alternateCustomerName !== '';
        http_response_code(isAdmin() || $usingAlternateName ? 400 : 409);
        exit($usingAlternateName || isAdmin()
            ? 'Please enter a customer name of 100 characters or fewer.'
            : 'Your account must have a valid name to make a reservation.');
    }

    $playType = $_POST['playType'] ?? '';
    $pcID = filter_input(INPUT_POST, 'pcID', FILTER_VALIDATE_INT);
    $pc = ($pcID !== false && $pcID !== null) ? getPCByID($pdo, $pcID) : null;
    if (!in_array($playType, ['Normal', 'VIP'], true) || !$pc) {
        http_response_code(400);
        exit('Please choose a valid PC.');
    }

    $expectedPcType = $playType === 'VIP' ? 'VIP' : 'Regular';
    if ($pc['pc_type'] !== $expectedPcType) {
        http_response_code(400);
        exit('Choose a PC from the selected play type.');
    }

    $hoursToPlay = filter_input(INPUT_POST, 'hoursToPlay', FILTER_VALIDATE_INT);
    if ($hoursToPlay === false || $hoursToPlay === null || $hoursToPlay < 0 || $hoursToPlay > 10) {
        http_response_code(400);
        exit('Please choose a valid play duration or Open Hour.');
    }

    $query = insertReservation(
        $pdo,
        getOrCreateCustomerID($pdo, $customerName, currentUserId()),
        $reservationDate,
        $playType,
        $pcID,
        $hoursToPlay,
        $_POST['notes'] ?? '',
        currentUserId()
    );

    if ($query) {
        header('Location: ../' . (isAdmin() ? 'admin.php' : 'index.php') . '?saved=1&customer=' . rawurlencode($customerName));
        exit;
    }

    echo 'Reservation failed';
}

if (isset($_POST['editReservationBtn'])) {
    requireAdmin();
    $customerName = trim($_POST['customerName'] ?? '');
    if ($customerName === '' || mb_strlen($customerName) > 100) {
        http_response_code(400);
        exit('Please enter a customer name of 100 characters or fewer.');
    }

    $playType = $_POST['playType'] ?? '';
    $pcID = filter_input(INPUT_POST, 'pcID', FILTER_VALIDATE_INT);
    $pc = ($pcID !== false && $pcID !== null) ? getPCByID($pdo, $pcID) : null;
    if (!in_array($playType, ['Normal', 'VIP'], true) || !$pc) {
        http_response_code(400);
        exit('Please choose a valid PC.');
    }

    $expectedPcType = $playType === 'VIP' ? 'VIP' : 'Regular';
    if ($pc['pc_type'] !== $expectedPcType) {
        http_response_code(400);
        exit('Choose a PC from the selected play type.');
    }

    $hoursToPlay = filter_input(INPUT_POST, 'hoursToPlay', FILTER_VALIDATE_INT);
    if ($hoursToPlay === false || $hoursToPlay === null || $hoursToPlay < 0 || $hoursToPlay > 10) {
        http_response_code(400);
        exit('Please choose a valid play duration or Open Hour.');
    }
    $status = $_POST['status'] ?? '';
    if (!in_array($status, ['Pending', 'Approved', 'Declined', 'Finished'], true)) {
        http_response_code(400);
        exit('Please choose a valid reservation status.');
    }

    $query = updateReservation(
        $pdo,
        getOrCreateCustomerID($pdo, $customerName, currentUserId()),
        $_POST['reservationDate'] ?? '',
        $playType,
        $pcID,
        $hoursToPlay,
        $_POST['notes'] ?? '',
        $status,
        filter_input(INPUT_GET, 'reservation_id', FILTER_VALIDATE_INT),
        currentUserId(),
        isAdmin()
    );

    if ($query) {
        header('Location: ../admin.php?updated=1');
        exit;
    }

    echo 'Reservation update failed';
}

if (isset($_POST['deleteReservationBtn'])) {
    requireLogin();
    $reservationId = filter_input(INPUT_GET, 'reservation_id', FILTER_VALIDATE_INT);
    if (!$reservationId) {
        http_response_code(400);
        exit('Invalid reservation.');
    }
    if (!isAdmin()) {
        $reservation = getReservationByID($pdo, $reservationId, currentUserId(), false);
        if (!$reservation || $reservation['status'] !== 'Pending') {
            http_response_code(403);
            exit('You can delete only your own pending reservations.');
        }
    }
    $query = deleteReservation($pdo, $reservationId, currentUserId(), isAdmin());

    if ($query) {
        header('Location: ../' . (isAdmin() ? 'admin.php' : 'index.php') . '?deleted=1');
        exit;
    }

    echo 'Reservation deletion failed';
}
