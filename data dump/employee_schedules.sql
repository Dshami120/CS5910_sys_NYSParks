CREATE TABLE Employee_Schedules (
    schedule_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    park_id INT NOT NULL,
    shift_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    schedule_status ENUM('scheduled', 'updated', 'cancelled', 'completed') NOT NULL DEFAULT 'scheduled',
    notes TEXT,
    CONSTRAINT fk_schedules_employee
        FOREIGN KEY (employee_id) REFERENCES Users(user_id),
    CONSTRAINT fk_schedules_park
        FOREIGN KEY (park_id) REFERENCES Parks(park_id),
    CONSTRAINT chk_schedules_time
        CHECK (start_time < end_time)
);
