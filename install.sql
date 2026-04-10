CREATE TABLE mod_invoice_recovery_logs (
id INT AUTO_INCREMENT PRIMARY KEY,
client_id INT,
action VARCHAR(255),
amount DECIMAL(10,2),
method VARCHAR(50),
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);