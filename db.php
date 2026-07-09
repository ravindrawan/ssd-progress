<?php
$host = 'ssd-extr-db';
$user = 'user7W1';                  // 'root' වෙනුවට අලුත් යූසර්
$pass = 'WkExocx6I5C8nWJv';          // පාස්වර්ඩ් එක ඇතුළත් කරන්න
$db = 'social_services_monthly';

$conn = @new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    if ($conn->connect_errno === 1049) {
        die("Error: The database '{$db}' does not exist. Please run the setup script in your browser to create and initialize the database: <a href='http://localhost/monthly%20progress/setup_db.php'>http://localhost/monthly progress/setup_db.php</a>");
    }
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$conn->set_charset('utf8mb4');
