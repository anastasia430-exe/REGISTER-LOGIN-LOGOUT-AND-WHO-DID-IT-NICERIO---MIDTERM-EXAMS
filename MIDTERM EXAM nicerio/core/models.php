<?php


// finds a user by username or email for login.
function getUserByLogin($pdo, $login) {
    $stmt = $pdo->prepare("SELECT user_id, first_name, last_name, username, role, password_hash
                           FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$login, $login]);
    return $stmt->fetch();
}

 //  create default admin baby
function ensureDefaultAdmin($pdo) {
    $adminUsername = 'admin';
    $adminEmail = 'admin@localhost';

    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE role = 'admin' ORDER BY user_id ASC LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();

    if ($admin) {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, first_name = 'admin', last_name = '', email = ?, role = 'admin' WHERE user_id = ?");
        $stmt->execute([$adminUsername, $adminEmail, $admin['user_id']]);
        return;
    }

    // default username password "admin"
    $stmt = $pdo->prepare("INSERT INTO users
        (username, first_name, last_name, email, password_hash, role)
        VALUES (?, 'admin', '', ?, ?, 'admin')");
    $stmt->execute([$adminUsername, $adminEmail, password_hash('admin', PASSWORD_DEFAULT)]);
}

// gumagawa ng account
function createUser($pdo, $username, $first_name, $last_name, $email, $password_hash, $address, $age, $phone) {
    $sql = "INSERT INTO users (username, first_name, last_name, email, password_hash, address, age, phone, role)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'user')";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$username, $first_name, $last_name, $email, $password_hash, $address ?: null, $age, $phone ?: null]);
}

function getUserById($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT user_id, username, first_name, last_name, email, password_hash, address, age, phone, role
                           FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function getAllUsers($pdo) {
    $stmt = $pdo->query("SELECT user_id, username, first_name, last_name, email, address, age, phone, role, created_at
                         FROM users ORDER BY user_id");
    return $stmt->fetchAll();
}

function getAllCustomers($pdo) {
    $stmt = $pdo->query("SELECT customers.customer_id, customers.customer_name,
                                COALESCE(customers.phone, (
                                    SELECT account.phone
                                    FROM users AS account
                                    WHERE TRIM(CONCAT(account.first_name, ' ', account.last_name)) = customers.customer_name
                                    ORDER BY account.user_id
                                    LIMIT 1
                                )) AS phone,
                                COALESCE(customers.email, (
                                    SELECT account.email
                                    FROM users AS account
                                    WHERE TRIM(CONCAT(account.first_name, ' ', account.last_name)) = customers.customer_name
                                    ORDER BY account.user_id
                                    LIMIT 1
                                )) AS email,
                                customers.last_updated,
                                (SELECT GROUP_CONCAT(reservations.reservation_date ORDER BY reservations.reservation_date SEPARATOR ', ')
                                 FROM reservations
                                 WHERE reservations.customer_id = customers.customer_id
                                   AND reservations.deleted_at IS NULL) AS reservation_dates,
                                creator.username AS added_by_username,
                                updater.username AS updated_by_username
                         FROM customers
                         LEFT JOIN users AS creator ON customers.added_by = creator.user_id
                         LEFT JOIN users AS updater ON customers.updated_by = updater.user_id
                         WHERE customers.deleted_at IS NULL
                         ORDER BY customers.customer_id");
    return $stmt->fetchAll();
}

function getDeletedCustomers($pdo) {
    $stmt = $pdo->query("SELECT customers.customer_id, customers.customer_name,
                                COALESCE(customers.phone, (
                                    SELECT account.phone
                                    FROM users AS account
                                    WHERE TRIM(CONCAT(account.first_name, ' ', account.last_name)) = customers.customer_name
                                    ORDER BY account.user_id
                                    LIMIT 1
                                )) AS phone,
                                COALESCE(customers.email, (
                                    SELECT account.email
                                    FROM users AS account
                                    WHERE TRIM(CONCAT(account.first_name, ' ', account.last_name)) = customers.customer_name
                                    ORDER BY account.user_id
                                    LIMIT 1
                                )) AS email,
                                customers.deleted_at,
                                (SELECT GROUP_CONCAT(reservations.reservation_date ORDER BY reservations.reservation_date SEPARATOR ', ')
                                 FROM reservations
                                 WHERE reservations.customer_id = customers.customer_id) AS reservation_dates,
                                deleter.username AS deleted_by_username
                         FROM customers
                         LEFT JOIN users AS deleter ON customers.deleted_by = deleter.user_id
                         WHERE customers.deleted_at IS NOT NULL
                         ORDER BY customers.deleted_at DESC, customers.customer_id DESC");
    return $stmt->fetchAll();
}

function getCustomerByID($pdo, $customer_id) {
    $stmt = $pdo->prepare("SELECT customers.customer_id, customers.customer_name,
                                  COALESCE(customers.phone, (
                                      SELECT account.phone
                                      FROM users AS account
                                      WHERE TRIM(CONCAT(account.first_name, ' ', account.last_name)) = customers.customer_name
                                      ORDER BY account.user_id
                                      LIMIT 1
                                  )) AS phone,
                                  COALESCE(customers.email, (
                                      SELECT account.email
                                      FROM users AS account
                                      WHERE TRIM(CONCAT(account.first_name, ' ', account.last_name)) = customers.customer_name
                                      ORDER BY account.user_id
                                      LIMIT 1
                                  )) AS email
                           FROM customers
                           WHERE customers.customer_id = ? AND customers.deleted_at IS NULL");
    $stmt->execute([$customer_id]);
    return $stmt->fetch();
}

function updateCustomer($pdo, $customer_id, $customer_name, $phone, $email, $user_id) {
    $stmt = $pdo->prepare("UPDATE customers
                           SET customer_name = ?, phone = ?, email = ?,
                               updated_by = ?, last_updated = CURRENT_TIMESTAMP
                           WHERE customer_id = ?");
    return $stmt->execute([$customer_name, $phone ?: null, $email ?: null, $user_id, $customer_id]);
}

function deleteCustomer($pdo, $customer_id, $user_id) {
    $stmt = $pdo->prepare("UPDATE customers
                           SET deleted_by = ?, deleted_at = CURRENT_TIMESTAMP
                           WHERE customer_id = ? AND deleted_at IS NULL");
    return $stmt->execute([$user_id, $customer_id]);
}

function restoreCustomer($pdo, $customer_id, $user_id) {
    $stmt = $pdo->prepare("UPDATE customers
                           SET deleted_at = NULL, updated_by = ?,
                               last_updated = CURRENT_TIMESTAMP
                           WHERE customer_id = ? AND deleted_at IS NOT NULL");
    return $stmt->execute([$user_id, $customer_id]);
}

function getDeletedReservations($pdo) {
    $stmt = $pdo->query("SELECT reservations.reservation_id, reservations.reservation_date,
                                reservations.status, reservations.date_added,
                                reservations.deleted_at, customers.customer_name,
                                deleter.username AS deleted_by_username,
                                creator.username AS added_by_username
                         FROM reservations
                         INNER JOIN customers ON reservations.customer_id = customers.customer_id
                         LEFT JOIN users AS deleter ON reservations.deleted_by = deleter.user_id
                         LEFT JOIN users AS creator ON reservations.added_by = creator.user_id
                         WHERE reservations.deleted_at IS NOT NULL
                         ORDER BY reservations.deleted_at DESC, reservations.reservation_id DESC");
    return $stmt->fetchAll();
}

function getFinishedReservations($pdo) {
    $stmt = $pdo->query("SELECT reservations.reservation_id, reservations.reservation_date,
    reservations.play_type, reservations.hours_to_play,
                reservations.notes, reservations.last_updated,
                                customers.customer_name, pcs.pc_type AS reserved_pc_type,
                                pcs.pc_number AS reserved_pc_number,
                                creator.first_name AS creator_first_name,
                                creator.last_name AS creator_last_name
                         FROM reservations
                         INNER JOIN customers ON reservations.customer_id = customers.customer_id
                         LEFT JOIN pcs ON reservations.pc_id = pcs.pc_id
                         LEFT JOIN users AS creator ON reservations.added_by = creator.user_id
                         WHERE reservations.status = 'Finished'
                           AND reservations.deleted_at IS NULL
                         ORDER BY reservations.last_updated DESC, reservations.reservation_id DESC");
    return $stmt->fetchAll();
}

function getManagedUserById($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT user_id, username, first_name, last_name, email, address, age, phone, role
                           FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function updateManagedUser($pdo, $user_id, $username, $first_name, $last_name, $email, $address, $age, $phone) {
    $sql = "UPDATE users SET username = ?, first_name = ?, last_name = ?, email = ?, address = ?, age = ?, phone = ?
            WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$username, $first_name, $last_name, $email, $address ?: null, $age, $phone ?: null, $user_id]);
}

function updateUserPassword($pdo, $user_id, $password_hash) {
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
    return $stmt->execute([$password_hash, $user_id]);
}

function updateOwnUserProfile($pdo, $user_id, $first_name, $last_name, $email, $address, $age, $phone) {
    $stmt = $pdo->prepare("UPDATE users
                           SET first_name = ?, last_name = ?, email = ?, address = ?,
                               age = ?, phone = ?
                           WHERE user_id = ?");
    return $stmt->execute([
        $first_name,
        $last_name,
        $email,
        $address ?: null,
        $age,
        $phone ?: null,
        $user_id
    ]);
}

// sinasabe kung sino gumawa o nagupdate
function insertReservation($pdo, $customer_id, $reservation_date, $play_type, $pc_id, $hours_to_play, $notes, $user_id) {
    $sql = "INSERT INTO reservations (customer_id, reservation_date, play_type, pc_id, hours_to_play, notes, status, added_by, updated_by)
        SELECT ?, ?, ?, ?, ?, ?, IF(role = 'admin', 'Approved', 'Pending'), ?, ?
        FROM users WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$customer_id, $reservation_date, $play_type, $pc_id, $hours_to_play, $notes, $user_id, $user_id, $user_id]);
}

function getReservations($pdo, $user_id, $is_admin) {
    // admin = lahat ng reservation user= sariling reservation lang.
    $sql = "SELECT reservations.*, customers.customer_name, pcs.pc_type AS reserved_pc_type, pcs.pc_number AS reserved_pc_number,
                   creator.first_name AS creator_first_name, creator.last_name AS creator_last_name,
                   updater.first_name AS updater_first_name, updater.last_name AS updater_last_name
            FROM reservations
            INNER JOIN customers ON reservations.customer_id = customers.customer_id
            LEFT JOIN pcs ON reservations.pc_id = pcs.pc_id
            LEFT JOIN users AS creator ON reservations.added_by = creator.user_id
            LEFT JOIN users AS updater ON reservations.updated_by = updater.user_id
            WHERE reservations.deleted_at IS NULL" .
            ($is_admin ? " AND reservations.status != 'Finished'" : " AND reservations.added_by = ?") . "
            ORDER BY reservations.reservation_date DESC, reservations.reservation_id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($is_admin ? [] : [$user_id]);
    return $stmt->fetchAll();
}

function getReservationByID($pdo, $reservation_id, $user_id = null, $is_admin = false) {
    $sql = "SELECT reservations.*, customers.customer_name, pcs.pc_type AS reserved_pc_type, pcs.pc_number AS reserved_pc_number
            FROM reservations
            INNER JOIN customers ON reservations.customer_id = customers.customer_id
            LEFT JOIN pcs ON reservations.pc_id = pcs.pc_id
            WHERE reservations.reservation_id = ? AND reservations.deleted_at IS NULL" .
            ($is_admin ? "" : " AND reservations.added_by = ?");
    $stmt = $pdo->prepare($sql);
    $stmt->execute($is_admin ? [$reservation_id] : [$reservation_id, $user_id]);
    return $stmt->fetch();
}

function getAllPCs($pdo) {
    $stmt = $pdo->query("SELECT pc_id, pc_type, pc_number FROM pcs ORDER BY pc_type, pc_number");
    return $stmt->fetchAll();
}

function getPCByID($pdo, $pc_id) {
    $stmt = $pdo->prepare("SELECT pc_id, pc_type, pc_number FROM pcs WHERE pc_id = ?");
    $stmt->execute([$pc_id]);
    return $stmt->fetch();
}

function getOrCreateCustomerID($pdo, $customer_name, $user_id) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO customers (customer_name, added_by, updated_by) VALUES (?, ?, ?)");
    $stmt->execute([$customer_name, $user_id, $user_id]);

    $stmt = $pdo->prepare("SELECT customer_id, deleted_at FROM customers WHERE customer_name = ? LIMIT 1");
    $stmt->execute([$customer_name]);
    $customer = $stmt->fetch();
    if (!$customer) {
        throw new RuntimeException('Unable to find or create the customer record.');
    }

    if ($customer['deleted_at'] !== null) {
        restoreCustomer($pdo, (int) $customer['customer_id'], $user_id);
    }

    return (int) $customer['customer_id'];
}

function updateReservation($pdo, $customer_id, $reservation_date, $play_type, $pc_id, $hours_to_play, $notes, $status, $reservation_id, $user_id, $is_admin) {
    $sql = "UPDATE reservations
            SET customer_id = ?,
                reservation_date = ?,
                play_type = ?,
                pc_id = ?,
                hours_to_play = ?,
                notes = ?,
                status = ?,
                updated_by = ?,
                last_updated = CURRENT_TIMESTAMP
            WHERE reservation_id = ? AND deleted_at IS NULL" .
            ($is_admin ? "" : " AND status = 'Pending'") .
            ($is_admin ? "" : " AND added_by = ?");
    $stmt = $pdo->prepare($sql);
    $params = [$customer_id, $reservation_date, $play_type, $pc_id, $hours_to_play, $notes, $status, $user_id, $reservation_id];
    if (!$is_admin) {
        $params[] = $user_id;
    }
    return $stmt->execute($params);
}

function deleteReservation($pdo, $reservation_id, $user_id, $is_admin) {
    // sinasave kunng kelan ginawa ung reservatiopn
    $sql = "UPDATE reservations SET deleted_by = ?, deleted_at = CURRENT_TIMESTAMP
            WHERE reservation_id = ? AND deleted_at IS NULL" .
            ($is_admin ? "" : " AND added_by = ? AND status = 'Pending'");
    $stmt = $pdo->prepare($sql);
    $params = [$user_id, $reservation_id];
    if (!$is_admin) {
        $params[] = $user_id;
    }
    return $stmt->execute($params);
}

function setReservationStatus($pdo, $reservation_id, $status, $user_id) {
    $currentStatus = $status === 'Finished' ? 'Approved' : 'Pending';
    if (!in_array($status, ['Approved', 'Declined', 'Finished'], true)) {
        return false;
    }

    $stmt = $pdo->prepare("UPDATE reservations
                           SET status = ?, updated_by = ?, last_updated = CURRENT_TIMESTAMP
                           WHERE reservation_id = ? AND status = ? AND deleted_at IS NULL");
    return $stmt->execute([$status, $user_id, $reservation_id, $currentStatus]);
}
