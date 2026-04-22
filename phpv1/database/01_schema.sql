-- NYS Parks & Recreation Capstone
-- Full schema for XAMPP / MySQL 8+
-- Import order:
--   00_create_database.sql
--   01_schema.sql
--   02_seed.sql

USE nys_parks;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS Analytics_Daily;
DROP TABLE IF EXISTS Payments;
DROP TABLE IF EXISTS Attendance;
DROP TABLE IF EXISTS PTO_Requests;
DROP TABLE IF EXISTS Employee_Schedules;
DROP TABLE IF EXISTS Bookings;
DROP TABLE IF EXISTS Events;
DROP TABLE IF EXISTS Fields;
DROP TABLE IF EXISTS Users;
DROP TABLE IF EXISTS Parks;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('client', 'employee', 'admin') NOT NULL,
    phone VARCHAR(20) NULL,
    park_id INT NULL,
    account_status ENUM('active', 'locked', 'disabled') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE Parks (
    park_id INT AUTO_INCREMENT PRIMARY KEY,
    park_name VARCHAR(150) NOT NULL UNIQUE,
    region VARCHAR(80) NOT NULL,
    park_type VARCHAR(60) NOT NULL DEFAULT 'Nature',
    address VARCHAR(180) NOT NULL,
    city VARCHAR(80) NOT NULL,
    state CHAR(2) NOT NULL DEFAULT 'NY',
    zip_code VARCHAR(10) NOT NULL,
    hours VARCHAR(120) NOT NULL,
    total_fields INT NOT NULL DEFAULT 1,
    max_capacity INT NOT NULL DEFAULT 100,
    amenities TEXT NULL,
    summary TEXT NULL,
    image_url VARCHAR(255) NULL,
    latitude DECIMAL(9,6) NULL,
    longitude DECIMAL(9,6) NULL,
    CONSTRAINT chk_parks_latitude CHECK (latitude IS NULL OR (latitude >= -90 AND latitude <= 90)),
    CONSTRAINT chk_parks_longitude CHECK (longitude IS NULL OR (longitude >= -180 AND longitude <= 180)),
    CONSTRAINT chk_parks_total_fields CHECK (total_fields >= 1),
    CONSTRAINT chk_parks_max_capacity CHECK (max_capacity >= 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE Users
    ADD CONSTRAINT fk_users_park FOREIGN KEY (park_id) REFERENCES Parks(park_id)
    ON DELETE SET NULL ON UPDATE CASCADE;

CREATE TABLE Fields (
    field_id INT AUTO_INCREMENT PRIMARY KEY,
    park_id INT NOT NULL,
    field_name VARCHAR(120) NOT NULL,
    field_type VARCHAR(60) NULL,
    field_size INT NULL,
    capacity INT NOT NULL,
    availability_status ENUM('available', 'unavailable', 'maintenance') NOT NULL DEFAULT 'available',
    notes TEXT NULL,
    CONSTRAINT fk_fields_park FOREIGN KEY (park_id) REFERENCES Parks(park_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_fields_capacity CHECK (capacity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE Events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    park_id INT NOT NULL,
    field_id INT NULL,
    booking_id INT NULL UNIQUE,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(60) NULL,
    event_type ENUM('public', 'private') NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    capacity INT NOT NULL DEFAULT 1,
    fee_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    image_url VARCHAR(255) NULL,
    event_status ENUM('draft', 'published', 'closed', 'cancelled', 'completed') NOT NULL DEFAULT 'draft',
    created_by INT NOT NULL,
    CONSTRAINT fk_events_park FOREIGN KEY (park_id) REFERENCES Parks(park_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_events_field FOREIGN KEY (field_id) REFERENCES Fields(field_id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_events_created_by FOREIGN KEY (created_by) REFERENCES Users(user_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_events_datetime CHECK (start_datetime < end_datetime),
    CONSTRAINT chk_events_capacity CHECK (capacity >= 1),
    CONSTRAINT chk_events_fee CHECK (fee_amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE Bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    park_id INT NOT NULL,
    event_id INT NOT NULL,
    field_id INT NOT NULL,
    attendee_email VARCHAR(255) NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    guest_count INT NOT NULL,
    special_requests TEXT NULL,
    booking_status ENUM('pending', 'approved', 'denied', 'confirmed', 'cancelled', 'expired') NOT NULL DEFAULT 'pending',
    reservation_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    reviewed_by INT NULL,
    decision_date DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_bookings_park FOREIGN KEY (park_id) REFERENCES Parks(park_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_bookings_event FOREIGN KEY (event_id) REFERENCES Events(event_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_bookings_field FOREIGN KEY (field_id) REFERENCES Fields(field_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_bookings_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES Users(user_id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_bookings_guest_count CHECK (guest_count >= 1),
    CONSTRAINT chk_bookings_reservation_fee CHECK (reservation_fee >= 0),
    CONSTRAINT chk_bookings_datetime CHECK (start_datetime < end_datetime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE Events
    ADD CONSTRAINT fk_events_booking FOREIGN KEY (booking_id) REFERENCES Bookings(booking_id)
    ON DELETE SET NULL ON UPDATE CASCADE;

CREATE TABLE Attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NULL,
    attendee_email VARCHAR(255) NOT NULL,
    guest_count INT NOT NULL,
    attendance_status ENUM('attending', 'cancelled') NOT NULL DEFAULT 'attending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_attendance_event FOREIGN KEY (event_id) REFERENCES Events(event_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_attendance_user FOREIGN KEY (user_id) REFERENCES Users(user_id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_attendance_guest_count CHECK (guest_count >= 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE Payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    booking_id INT NULL,
    payment_type ENUM('reservation', 'donation') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('card', 'external_processor', 'paypal', 'apple_pay') NOT NULL DEFAULT 'card',
    transaction_ref VARCHAR(100) NULL UNIQUE,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_user FOREIGN KEY (user_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_payments_booking FOREIGN KEY (booking_id) REFERENCES Bookings(booking_id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_payments_amount CHECK (amount > 0),
    CONSTRAINT chk_payments_booking_required CHECK (
        (payment_type = 'reservation' AND booking_id IS NOT NULL)
        OR
        (payment_type = 'donation' AND booking_id IS NULL)
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE Employee_Schedules (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    park_id INT NOT NULL,
    shift_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    schedule_status ENUM('scheduled', 'updated', 'cancelled', 'completed') NOT NULL DEFAULT 'scheduled',
    notes TEXT NULL,
    CONSTRAINT fk_schedules_employee FOREIGN KEY (employee_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_schedules_park FOREIGN KEY (park_id) REFERENCES Parks(park_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_schedules_time CHECK (start_time < end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE PTO_Requests (
    pto_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT NULL,
    pto_status ENUM('pending', 'approved', 'denied') NOT NULL DEFAULT 'pending',
    reviewed_by INT NULL,
    decision_date DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pto_employee FOREIGN KEY (employee_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pto_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES Users(user_id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_pto_dates CHECK (start_date <= end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE Analytics_Daily (
    analytics_id INT AUTO_INCREMENT PRIMARY KEY,
    metric_date DATE NOT NULL,
    park_id INT NULL,
    site_visits INT NOT NULL DEFAULT 0,
    bookings_created INT NOT NULL DEFAULT 0,
    park_visits INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_analytics_park FOREIGN KEY (park_id) REFERENCES Parks(park_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT uq_analytics_site UNIQUE (metric_date, park_id),
    CONSTRAINT chk_analytics_site_visits CHECK (site_visits >= 0),
    CONSTRAINT chk_analytics_bookings CHECK (bookings_created >= 0),
    CONSTRAINT chk_analytics_park_visits CHECK (park_visits >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_events_start ON Events(start_datetime);
CREATE INDEX idx_events_status ON Events(event_status);
CREATE INDEX idx_bookings_status ON Bookings(booking_status);
CREATE INDEX idx_bookings_user ON Bookings(user_id);
CREATE INDEX idx_schedules_employee_date ON Employee_Schedules(employee_id, shift_date);
CREATE INDEX idx_pto_employee_status ON PTO_Requests(employee_id, pto_status);
CREATE INDEX idx_analytics_date ON Analytics_Daily(metric_date);
