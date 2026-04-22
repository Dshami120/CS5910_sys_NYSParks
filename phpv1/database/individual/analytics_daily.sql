USE nys_parks;

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
