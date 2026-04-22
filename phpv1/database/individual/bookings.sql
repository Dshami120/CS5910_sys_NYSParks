USE nys_parks;

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

