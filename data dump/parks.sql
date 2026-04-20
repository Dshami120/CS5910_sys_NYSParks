CREATE TABLE Parks (
    park_id INT PRIMARY KEY AUTO_INCREMENT,
    park_name VARCHAR(150) NOT NULL UNIQUE,
    region VARCHAR(80) NOT NULL,
    address VARCHAR(180) NOT NULL,
    city VARCHAR(80) NOT NULL,
    state CHAR(2) NOT NULL DEFAULT 'NY',
    zip_code VARCHAR(10) NOT NULL,
    hours VARCHAR(120) NOT NULL,
    amenities TEXT,
    latitude DECIMAL(9,6),
    longitude DECIMAL(9,6),
    CONSTRAINT chk_parks_latitude
        CHECK (latitude IS NULL OR (latitude >= -90 AND latitude <= 90)),
    CONSTRAINT chk_parks_longitude
        CHECK (longitude IS NULL OR (longitude >= -180 AND longitude <= 180))
);

ALTER TABLE Users
ADD CONSTRAINT fk_users_park
FOREIGN KEY (park_id) REFERENCES Parks(park_id);
