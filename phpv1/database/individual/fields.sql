USE nys_parks;

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
