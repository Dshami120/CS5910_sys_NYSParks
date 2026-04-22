USE nys_parks;

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
