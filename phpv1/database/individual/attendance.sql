USE nys_parks;

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
