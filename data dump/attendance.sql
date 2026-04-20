CREATE TABLE Attendance (
    attendance_id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NULL,
    attendee_email VARCHAR(255) NOT NULL,
    guest_count INT NOT NULL,
    attendance_status ENUM('attending', 'cancelled') NOT NULL DEFAULT 'attending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_attendance_event
        FOREIGN KEY (event_id) REFERENCES Events(event_id),
    CONSTRAINT fk_attendance_user
        FOREIGN KEY (user_id) REFERENCES Users(user_id),
    CONSTRAINT chk_attendance_guest_count
        CHECK (guest_count >= 1),
    CONSTRAINT chk_attendance_email
        CHECK (attendee_email LIKE '%@%.%'),
);
