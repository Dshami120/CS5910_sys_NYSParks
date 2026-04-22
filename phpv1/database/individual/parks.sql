USE nys_parks;

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

