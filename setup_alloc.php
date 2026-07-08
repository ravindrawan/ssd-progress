<?php
require 'db.php';
$conn->query("CREATE TABLE IF NOT EXISTS allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ag_office_id INT NOT NULL,
    year INT NOT NULL,
    assistance_type ENUM('financial', 'equipment') NOT NULL,
    allocated_amount DECIMAL(15,2) DEFAULT 0.00,
    UNIQUE KEY unique_alloc (ag_office_id, year, assistance_type),
    FOREIGN KEY (ag_office_id) REFERENCES ag_offices(id)
)");
echo $conn->error;
?>
