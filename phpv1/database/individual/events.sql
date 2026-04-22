USE nys_parks;

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
