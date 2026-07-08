<?php
require 'auth.php';
require_login();
if (get_user_role() !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Only superadmin can clear data.']);
    exit;
}

require 'db.php';
header('Content-Type: application/json; charset=utf-8');

// Clear assistance records
if ($conn->query('TRUNCATE TABLE assistance_records') === FALSE) {
    echo json_encode(['success'=>false, 'error'=>'Failed to truncate assistance_records: ' . $conn->error]);
    exit;
}
// Clear allocations if the table exists
if ($conn->query('SHOW TABLES LIKE "allocations"')->num_rows > 0) {
    if ($conn->query('TRUNCATE TABLE allocations') === FALSE) {
        echo json_encode(['success'=>false, 'error'=>'Failed to truncate allocations: ' . $conn->error]);
        exit;
    }
}

echo json_encode(['success'=>true, 'message'=>'All data cleared']);
?>
