CREATE TABLE Payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    booking_id INT NULL,
    payment_type ENUM('reservation', 'donation') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    card_number CHAR(16) NOT NULL,
    exp_date DATE NOT NULL,
    cvv CHAR(3) NOT NULL,
    payment_status ENUM('pending', ‘completed’, 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    transaction_ref VARCHAR(100) UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_user
        FOREIGN KEY (user_id) REFERENCES Users(user_id),
    CONSTRAINT fk_payments_booking
        FOREIGN KEY (booking_id) REFERENCES Bookings(booking_id),
    CONSTRAINT chk_payments_amount
        CHECK (amount > 0),
    CONSTRAINT chk_payments_card_number
        CHECK (card_number REGEXP '^[0-9]{16}$'),
    CONSTRAINT chk_payments_cvv
        CHECK (cvv REGEXP '^[0-9]{3}$'),
    CONSTRAINT chk_payments_booking_required
        CHECK (
            (payment_type = 'reservation' AND booking_id IS NOT NULL)
            OR
            (payment_type = 'donation' AND booking_id IS NULL)
        )
);
