CREATE TABLE Fields (
    field_id INT PRIMARY KEY AUTO_INCREMENT,
    park_id INT NOT NULL,
    field_name VARCHAR(120) NOT NULL,
    field_type VARCHAR(60),
    capacity INT NOT NULL,
    availability_status ENUM('available', 'unavailable', 'maintenance') NOT NULL DEFAULT 'available',
    notes TEXT,
    CONSTRAINT fk_fields_park
        FOREIGN KEY (park_id) REFERENCES Parks(park_id),
    CONSTRAINT chk_fields_capacity
        CHECK (capacity > 0
