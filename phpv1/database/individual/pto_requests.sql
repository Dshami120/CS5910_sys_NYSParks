USE nys_parks;

CREATE TABLE PTO_Requests (
    pto_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT NULL,
    pto_status ENUM('pending', 'approved', 'denied') NOT NULL DEFAULT 'pending',
    reviewed_by INT NULL,
    decision_date DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pto_employee FOREIGN KEY (employee_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pto_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES Users(user_id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_pto_dates CHECK (start_date <= end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
