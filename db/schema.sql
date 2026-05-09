-- NYS Parks static-site database schema
-- Clean full rebuild schema based on the approved comparison decisions.
-- Assumptions:
--   1. events is the base table for all events.
--   2. public events are normal park/program events.
--   3. private events are created when an event booking is approved.
--   4. bookings.event_id is the only booking/event relationship. Do not add events.booking_id.
--   5. All events are visible; event_type only identifies public vs private.

DROP DATABASE IF EXISTS nys_parks;
CREATE DATABASE nys_parks CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nys_parks;

-- Parent table for park locations. Includes image/card fields for park cards.
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
    total_fields INT NOT NULL DEFAULT 1,
    max_capacity INT NOT NULL DEFAULT 1,
    amenities TEXT NULL,
    description TEXT NOT NULL,
    image_url VARCHAR(255) NULL,
    image_alt VARCHAR(180) NULL,
    card_summary VARCHAR(255) NULL,
    latitude DECIMAL(9,6) NULL,
    longitude DECIMAL(9,6) NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_parks_total_fields CHECK (total_fields >= 0),
    CONSTRAINT chk_parks_max_capacity CHECK (max_capacity > 0),
    INDEX idx_parks_region (region),
    INDEX idx_parks_type (park_type),
    INDEX idx_parks_featured (is_featured)
) ENGINE=InnoDB;

-- Users includes clients, employees, and admins.
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
    profile_image_url VARCHAR(255) NULL,
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

-- Fields/facilities belong to parks. Bookings may optionally request a specific field.
CREATE TABLE fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    park_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    field_type VARCHAR(60) NOT NULL,
    capacity INT NOT NULL,
    field_size_sqft INT NULL,
    availability_status ENUM('available','unavailable','maintenance') NOT NULL DEFAULT 'available',
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_fields_park FOREIGN KEY (park_id) REFERENCES parks(id) ON DELETE CASCADE,
    CONSTRAINT chk_fields_capacity CHECK (capacity > 0),
    CONSTRAINT chk_fields_size CHECK (field_size_sqft IS NULL OR field_size_sqft > 0),
    UNIQUE KEY uq_field_name_per_park (park_id, name),
    INDEX idx_fields_park_status (park_id, availability_status)
) ENGINE=InnoDB;

-- Base table for public and private events. Includes image_url/is_featured for event image cards.
-- Private events are created from approved booking requests.
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    park_id INT NOT NULL,
    field_id INT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    image_url VARCHAR(255) NULL,
    image_alt VARCHAR(180) NULL,
    card_summary VARCHAR(255) NULL,
    category VARCHAR(60) NOT NULL,
    event_type ENUM('public','private') NOT NULL DEFAULT 'public',
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    capacity INT NOT NULL DEFAULT 1,
    fee_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    event_status ENUM('draft','published','closed','cancelled','completed') NOT NULL DEFAULT 'published',
    created_by INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_events_park FOREIGN KEY (park_id) REFERENCES parks(id) ON DELETE RESTRICT,
    CONSTRAINT fk_events_field FOREIGN KEY (field_id) REFERENCES fields(id) ON DELETE SET NULL,
    CONSTRAINT fk_events_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT chk_events_dates CHECK (start_datetime < end_datetime),
    CONSTRAINT chk_events_capacity CHECK (capacity > 0),
    CONSTRAINT chk_events_fee CHECK (fee_amount >= 0),
    INDEX idx_events_status_dates (event_status, start_datetime),
    INDEX idx_events_park_dates (park_id, start_datetime),
    INDEX idx_events_type_status_dates (event_type, event_status, start_datetime),
    INDEX idx_events_category (category),
    INDEX idx_events_featured (is_featured)
) ENGINE=InnoDB;

-- Public news feed items used by the News page. Includes image/card fields for news cards.
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    topic ENUM('alerts','community','events','parks','safety','support','conservation','volunteer','maintenance','education','seasonal') NOT NULL,
    published_date DATE NOT NULL,
    region VARCHAR(80) NOT NULL,
    summary TEXT NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255) NULL,
    image_alt VARCHAR(180) NULL,
    card_summary VARCHAR(255) NULL,
    tag VARCHAR(60) NOT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    news_status ENUM('draft','published','archived') NOT NULL DEFAULT 'published',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_news_status_date (news_status, published_date),
    INDEX idx_news_topic_date (topic, published_date),
    INDEX idx_news_region (region),
    INDEX idx_news_featured (is_featured)
) ENGINE=InnoDB;

-- Client event-booking requests.
-- If approved, create a private row in events, then store that event id here.
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    park_id INT NOT NULL,
    field_id INT NULL,
    event_id INT NULL,
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
    booking_status ENUM('pending','approved','denied','cancelled','confirmed','completed') NOT NULL DEFAULT 'pending',
    reviewed_by INT NULL,
    reviewed_at DATETIME NULL,
    admin_notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_bookings_park FOREIGN KEY (park_id) REFERENCES parks(id) ON DELETE RESTRICT,
    CONSTRAINT fk_bookings_field FOREIGN KEY (field_id) REFERENCES fields(id) ON DELETE SET NULL,
    CONSTRAINT fk_bookings_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE SET NULL,
    CONSTRAINT fk_bookings_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT chk_bookings_dates CHECK (start_datetime < end_datetime),
    CONSTRAINT chk_bookings_guest_count CHECK (guest_count > 0),
    CONSTRAINT chk_bookings_fee CHECK (reservation_fee >= 0),
    UNIQUE KEY uq_bookings_event (event_id),
    INDEX idx_bookings_status_dates (booking_status, start_datetime),
    INDEX idx_bookings_client_status (client_id, booking_status),
    INDEX idx_bookings_park_dates (park_id, start_datetime),
    INDEX idx_bookings_reviewed_by (reviewed_by)
) ENGINE=InnoDB;

-- RSVP / attendance / check-in records for events.
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NULL,
    attendee_email VARCHAR(255) NOT NULL,
    guest_count INT NOT NULL DEFAULT 1,
    attendance_status ENUM('registered','cancelled','attended','no_show') NOT NULL DEFAULT 'registered',
    registered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    checked_in_at DATETIME NULL,
    CONSTRAINT fk_attendance_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_attendance_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT chk_attendance_guest_count CHECK (guest_count > 0),
    UNIQUE KEY uq_attendance_event_email (event_id, attendee_email),
    INDEX idx_attendance_event_status (event_id, attendance_status),
    INDEX idx_attendance_user (user_id),
    INDEX idx_attendance_email (attendee_email)
) ENGINE=InnoDB;

-- Mock payment records for reservation fees and donations.
-- This project stores card fields only for mock SQL validation/testing.
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    booking_id INT NULL,
    payment_type ENUM('reservation','donation') NOT NULL,
    payer_name VARCHAR(120) NOT NULL,
    payer_email VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('card','in_person') NOT NULL DEFAULT 'card',
    card_num VARCHAR(19) NULL,
    exp_month TINYINT NULL,
    exp_year SMALLINT NULL,
    cvv VARCHAR(4) NULL,
    payment_status ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'completed',
    transaction_ref VARCHAR(100) NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_payments_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    CONSTRAINT chk_payments_amount CHECK (amount > 0),
    CONSTRAINT chk_payments_card_num CHECK (payment_method <> 'card' OR (card_num IS NOT NULL AND card_num REGEXP '^[0-9]{13,19}$')),
    CONSTRAINT chk_payments_exp_month CHECK (payment_method <> 'card' OR (exp_month IS NOT NULL AND exp_month BETWEEN 1 AND 12)),
    CONSTRAINT chk_payments_exp_year CHECK (payment_method <> 'card' OR (exp_year IS NOT NULL AND exp_year BETWEEN 2026 AND 2100)),
    CONSTRAINT chk_payments_cvv CHECK (payment_method <> 'card' OR (cvv IS NOT NULL AND cvv REGEXP '^[0-9]{3,4}$')),
    INDEX idx_payments_type_status (payment_type, payment_status),
    INDEX idx_payments_user (user_id),
    INDEX idx_payments_booking (booking_id),
    INDEX idx_payments_date (created_at)
) ENGINE=InnoDB;

-- Employee work schedules.
CREATE TABLE employee_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    park_id INT NOT NULL,
    shift_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    assignment VARCHAR(120) NOT NULL,
    schedule_status ENUM('scheduled','cancelled','completed') NOT NULL DEFAULT 'scheduled',
    notes TEXT NULL,
    created_by INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_sched_employee FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_sched_park FOREIGN KEY (park_id) REFERENCES parks(id) ON DELETE RESTRICT,
    CONSTRAINT fk_sched_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT chk_sched_times CHECK (start_time < end_time),
    INDEX idx_sched_employee_date (employee_id, shift_date),
    INDEX idx_sched_park_date (park_id, shift_date),
    INDEX idx_sched_status (schedule_status)
) ENGINE=InnoDB;

-- Employee PTO requests.
CREATE TABLE pto_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    leave_type VARCHAR(60) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT NULL,
    pto_status ENUM('pending','approved','denied','cancelled') NOT NULL DEFAULT 'pending',
    reviewed_by INT NULL,
    reviewed_at DATETIME NULL,
    admin_notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pto_employee FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_pto_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT chk_pto_dates CHECK (start_date <= end_date),
    INDEX idx_pto_employee_status (employee_id, pto_status),
    INDEX idx_pto_dates_status (start_date, end_date, pto_status),
    INDEX idx_pto_reviewed_by (reviewed_by)
) ENGINE=InnoDB;

-- Recommended booking approval flow:
-- 1. INSERT INTO bookings (..., booking_status) VALUES (..., 'pending');
-- 2. Admin approves request.
-- 3. INSERT INTO events (..., event_type, event_status)
--    VALUES (..., 'private', 'published');
-- 4. UPDATE bookings
--    SET event_id = <new_private_event_id>,
--        booking_status = 'approved',
--        reviewed_by = <admin_user_id>,
--        reviewed_at = NOW()
--    WHERE id = <booking_id>;


-- Dynamic/business validations intentionally handled in PHP instead of triggers:
--   1. Card expiration must not be before the current month/year.
--   2. Event capacity should not exceed selected field capacity.
--   3. Booking guest count should not exceed selected field capacity.
--   4. Active bookings should not overlap for the same field and time range.
--   5. Attendance guest totals should not exceed event capacity.
-- This keeps the schema import-safe for phpMyAdmin, MySQL CLI, Workbench,
-- and simple PHP import scripts that split SQL by semicolon.
