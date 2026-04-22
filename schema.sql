DROP DATABASE IF EXISTS nys_parks;
CREATE DATABASE nys_parks CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nys_parks;

CREATE TABLE parks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    region VARCHAR(80) NOT NULL,
    park_type VARCHAR(60) NOT NULL,
    address_line VARCHAR(180) NOT NULL,
    city VARCHAR(80) NOT NULL,
    state CHAR(2) NOT NULL DEFAULT 'NY',
    zip_code VARCHAR(10) NOT NULL,
    hours VARCHAR(120) NOT NULL,
    amenities TEXT NULL,
    description TEXT NOT NULL,
    image_url VARCHAR(255) NULL,
    latitude DECIMAL(9,6) NULL,
    longitude DECIMAL(9,6) NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_parks_region (region),
    INDEX idx_parks_type (park_type),
    INDEX idx_parks_featured (is_featured)
) ENGINE=InnoDB;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('client','employee','admin') NOT NULL,
    phone VARCHAR(25) NULL,
    birthdate DATE NULL,
    organization VARCHAR(120) NULL,
    notes TEXT NULL,
    park_id INT NULL,
    account_status ENUM('active','locked','disabled') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login_at DATETIME NULL,
    CONSTRAINT fk_users_park FOREIGN KEY (park_id) REFERENCES parks(id) ON DELETE SET NULL,
    INDEX idx_users_role_status (role, account_status),
    INDEX idx_users_email_status (email, account_status),
    INDEX idx_users_park (park_id)
) ENGINE=InnoDB;

CREATE TABLE fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    park_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    field_type VARCHAR(60) NOT NULL,
    capacity INT NOT NULL,
    availability_status ENUM('available','unavailable','maintenance') NOT NULL DEFAULT 'available',
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fields_park FOREIGN KEY (park_id) REFERENCES parks(id) ON DELETE CASCADE,
    UNIQUE KEY uq_field_name_per_park (park_id, name),
    INDEX idx_fields_park_status (park_id, availability_status)
) ENGINE=InnoDB;

CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    park_id INT NOT NULL,
    field_id INT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(60) NOT NULL,
    event_type ENUM('public','private') NOT NULL DEFAULT 'public',
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    ticket_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    event_status ENUM('draft','published','closed','cancelled','completed') NOT NULL DEFAULT 'published',
    created_by INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_events_park FOREIGN KEY (park_id) REFERENCES parks(id) ON DELETE RESTRICT,
    CONSTRAINT fk_events_field FOREIGN KEY (field_id) REFERENCES fields(id) ON DELETE SET NULL,
    CONSTRAINT fk_events_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_events_status_dates (event_status, start_datetime),
    INDEX idx_events_park_dates (park_id, start_datetime),
    INDEX idx_events_category (category)
) ENGINE=InnoDB;

CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    park_id INT NOT NULL,
    field_id INT NULL,
    title VARCHAR(150) NOT NULL,
    booking_type VARCHAR(60) NOT NULL,
    attendee_email VARCHAR(255) NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    guest_count INT NOT NULL,
    requested_setup VARCHAR(120) NULL,
    event_description TEXT NOT NULL,
    special_requests TEXT NULL,
    reservation_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    booking_status ENUM('pending','approved','denied','confirmed','cancelled') NOT NULL DEFAULT 'pending',
    reviewed_by INT NULL,
    reviewed_at DATETIME NULL,
    admin_notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_bookings_park FOREIGN KEY (park_id) REFERENCES parks(id) ON DELETE RESTRICT,
    CONSTRAINT fk_bookings_field FOREIGN KEY (field_id) REFERENCES fields(id) ON DELETE SET NULL,
    CONSTRAINT fk_bookings_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_bookings_status_dates (booking_status, start_datetime),
    INDEX idx_bookings_client_status (client_id, booking_status),
    INDEX idx_bookings_park_dates (park_id, start_datetime),
    INDEX idx_bookings_reviewed_by (reviewed_by)
) ENGINE=InnoDB;

CREATE TABLE employee_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    park_id INT NOT NULL,
    shift_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    assignment VARCHAR(120) NOT NULL,
    schedule_status ENUM('scheduled','updated','cancelled','completed') NOT NULL DEFAULT 'scheduled',
    notes TEXT NULL,
    created_by INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_sched_employee FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_sched_park FOREIGN KEY (park_id) REFERENCES parks(id) ON DELETE RESTRICT,
    CONSTRAINT fk_sched_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_sched_employee_date (employee_id, shift_date),
    INDEX idx_sched_park_date (park_id, shift_date),
    INDEX idx_sched_status (schedule_status)
) ENGINE=InnoDB;

CREATE TABLE pto_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    leave_type VARCHAR(60) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT NULL,
    pto_status ENUM('pending','approved','denied') NOT NULL DEFAULT 'pending',
    reviewed_by INT NULL,
    reviewed_at DATETIME NULL,
    admin_notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pto_employee FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_pto_reviewed FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_pto_employee_status (employee_id, pto_status),
    INDEX idx_pto_dates_status (start_date, end_date, pto_status),
    INDEX idx_pto_reviewed_by (reviewed_by)
) ENGINE=InnoDB;

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    booking_id INT NULL,
    payment_type ENUM('reservation','donation') NOT NULL,
    donor_name VARCHAR(120) NOT NULL,
    donor_email VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    card_last4 CHAR(4) NULL,
    card_brand VARCHAR(30) NULL,
    exp_month TINYINT UNSIGNED NULL,
    exp_year SMALLINT UNSIGNED NULL,
    payment_method ENUM('card','in_person') NOT NULL DEFAULT 'card',
    payment_status ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'completed',
    transaction_ref VARCHAR(100) NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_payments_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    INDEX idx_payments_type_status (payment_type, payment_status),
    INDEX idx_payments_user (user_id),
    INDEX idx_payments_booking (booking_id),
    INDEX idx_payments_date (created_at)
) ENGINE=InnoDB;

CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NULL,
    attendee_email VARCHAR(255) NOT NULL,
    guest_count INT NOT NULL DEFAULT 1,
    attendance_status ENUM('attending','cancelled') NOT NULL DEFAULT 'attending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_att_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_att_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uq_attendance (event_id, attendee_email),
    INDEX idx_att_event (event_id),
    INDEX idx_att_user (user_id)
) ENGINE=InnoDB;
