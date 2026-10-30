--accountt
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    address VARCHAR(255),
    age TINYINT UNSIGNED,
    phone VARCHAR(30),
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- pangalan lang ng customer hindi sa login account
CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    email VARCHAR(255) NULL,
    added_by INT NOT NULL,
    updated_by INT NOT NULL,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_customers_added_by
        FOREIGN KEY (added_by) REFERENCES users(user_id),
    CONSTRAINT fk_customers_updated_by
        FOREIGN KEY (updated_by) REFERENCES users(user_id),
    CONSTRAINT fk_customers_deleted_by
        FOREIGN KEY (deleted_by) REFERENCES users(user_id)
);

CREATE TABLE pcs (
    pc_id INT AUTO_INCREMENT PRIMARY KEY,
    pc_type ENUM('Regular', 'VIP') NOT NULL,
    pc_number INT NOT NULL,
    UNIQUE KEY uq_pcs_type_number (pc_type, pc_number)
);

INSERT INTO pcs (pc_type, pc_number) VALUES
    ('Regular', 1), ('Regular', 2), ('Regular', 3), ('Regular', 4), ('Regular', 5),
    ('Regular', 6), ('Regular', 7), ('Regular', 8), ('Regular', 9), ('Regular', 10),
    ('Regular', 11), ('Regular', 12), ('Regular', 13), ('Regular', 14), ('Regular', 15),
    ('Regular', 16), ('Regular', 17), ('Regular', 18), ('Regular', 19), ('Regular', 20),
    ('VIP', 20), ('VIP', 21), ('VIP', 22), ('VIP', 23), ('VIP', 24),
    ('VIP', 25), ('VIP', 26), ('VIP', 27), ('VIP', 28), ('VIP', 29),
    ('VIP', 30), ('VIP', 31), ('VIP', 32), ('VIP', 33), ('VIP', 34),
    ('VIP', 35), ('VIP', 36), ('VIP', 37), ('VIP', 38), ('VIP', 39), ('VIP', 40);

-- reservation nag-update nag-delete
CREATE TABLE reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    reservation_date DATE NOT NULL,
    play_type ENUM('Normal', 'VIP') NOT NULL DEFAULT 'Normal',
    pc_id INT NOT NULL,
    hours_to_play INT NOT NULL DEFAULT 1,
    notes TEXT,
    status ENUM('Pending', 'Approved', 'Declined', 'Finished') NOT NULL DEFAULT 'Pending',
    date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    added_by INT NOT NULL, 
    updated_by INT NOT NULL, 
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_reservations_customers
        FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    CONSTRAINT fk_reservations_pcs
        FOREIGN KEY (pc_id) REFERENCES pcs(pc_id),
    CONSTRAINT fk_reservations_added_by
        FOREIGN KEY (added_by) REFERENCES users(user_id),
    CONSTRAINT fk_reservations_updated_by
        FOREIGN KEY (updated_by) REFERENCES users(user_id),
    CONSTRAINT fk_reservations_deleted_by
        FOREIGN KEY (deleted_by) REFERENCES users(user_id),
    INDEX idx_reservations_deleted_at (deleted_at),
    INDEX idx_reservations_date (reservation_date)
);
