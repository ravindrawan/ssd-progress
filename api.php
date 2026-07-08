<?php
require 'db.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$type = $_GET['type'] ?? 'financial';

if ($action == 'get_offices') {
    $result = $conn->query("SELECT id, name FROM ag_offices ORDER BY district, name");
    $offices = [];
    while ($row = $result->fetch_assoc()) {
        $offices[] = $row;
    }
    echo json_encode($offices);
    exit;
}

if ($action == 'get_records') {
    $ag_office_id = (int)($_GET['ag_office_id'] ?? 0);
    $year = (int)($_GET['year'] ?? date('Y'));
    $month = (int)($_GET['month'] ?? date('n'));


    $sql = "SELECT * FROM assistance_records WHERE ag_office_id = ? AND year = ? AND month = ? AND assistance_type = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiis', $ag_office_id, $year, $month, $type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $records = [];
    $exists = ($result->num_rows > 0);
    while ($row = $result->fetch_assoc()) {
        $records[$row['category']] = $row;
    }

    // Now calculate cumulative amount up to this month for the given year
    // cumulative means sum(amount) where year = ? and month <= ?
    $sql_cum = "SELECT category, SUM(amount) as cumulative_amount, SUM(actual_beneficiaries) as cumulative_act FROM assistance_records WHERE ag_office_id = ? AND year = ? AND month <= ? AND assistance_type = ? GROUP BY category";
    $stmt_cum = $conn->prepare($sql_cum);
    $stmt_cum->bind_param('iiis', $ag_office_id, $year, $month, $type);
    $stmt_cum->execute();
    $result_cum = $stmt_cum->get_result();
    
    $cumulative = [];
    while ($row = $result_cum->fetch_assoc()) {
        $cumulative[$row['category']] = [
            'amount' => $row['cumulative_amount'],
            'act' => $row['cumulative_act']
        ];
    }

    // Fetch max estimate and max allocated_amount for the year so they persist across months
    $sql_est = "SELECT category, MAX(estimated_beneficiaries) as max_est, MAX(allocated_amount) as max_alloc FROM assistance_records WHERE ag_office_id = ? AND year = ? AND assistance_type = ? GROUP BY category";
    $stmt_est = $conn->prepare($sql_est);
    $stmt_est->bind_param('iis', $ag_office_id, $year, $type);
    $stmt_est->execute();
    $result_est = $stmt_est->get_result();
    
    $max_allocs = [];
    while ($row = $result_est->fetch_assoc()) {
        $cat = $row['category'];
        $max_allocs[$cat] = $row['max_alloc'];
        if (!isset($records[$cat])) {
            $records[$cat] = [
                'category' => $cat,
                'estimated_beneficiaries' => $row['max_est'],
                'actual_beneficiaries' => 0,
                'amount' => 0,
                'allocated_amount' => $row['max_alloc']
            ];
        } else {
            if ($records[$cat]['estimated_beneficiaries'] == 0) {
                $records[$cat]['estimated_beneficiaries'] = $row['max_est'];
            }
            if (!isset($records[$cat]['allocated_amount']) || $records[$cat]['allocated_amount'] == 0) {
                $records[$cat]['allocated_amount'] = $row['max_alloc'];
            }
        }
    }

    foreach ($records as $cat => &$rec) {
        $rec['cumulative_amount'] = $cumulative[$cat]['amount'] ?? 0;
        $rec['cumulative_act'] = $cumulative[$cat]['act'] ?? 0;
        if (!isset($rec['allocated_amount']) || (float)$rec['allocated_amount'] == 0) {
            $rec['allocated_amount'] = $max_allocs[$cat] ?? 0;
        }
    }

    // Fetch allocated amount for the year
    $sql_alloc = "SELECT allocated_amount FROM allocations WHERE ag_office_id = ? AND year = ? AND assistance_type = ?";
    $stmt_alloc = $conn->prepare($sql_alloc);
    $stmt_alloc->bind_param('iis', $ag_office_id, $year, $type);
    $stmt_alloc->execute();
    $result_alloc = $stmt_alloc->get_result();
    $allocated_amount = 0;
    if ($row_alloc = $result_alloc->fetch_assoc()) {
        $allocated_amount = $row_alloc['allocated_amount'];
    }

    echo json_encode(['records' => $records, 'cumulative' => $cumulative, 'allocated_amount' => $allocated_amount, 'exists' => $exists]);
    exit;
}

if ($action == 'get_summary') {
    $year = (int)($_GET['year'] ?? date('Y'));
    $month = (int)($_GET['month'] ?? date('n'));
    $ag_office_id = $_GET['ag_office_id'] ?? 'all';

    $office_filter = ($ag_office_id !== 'all') ? " AND ag_office_id = ?" : "";

    $sql = "SELECT category, SUM(actual_beneficiaries) as act, SUM(amount) as amt, SUM(allocated_amount) as alloc FROM assistance_records WHERE year = ? AND month = ? AND assistance_type = ? $office_filter GROUP BY category";
    $stmt = $conn->prepare($sql);
    if ($ag_office_id !== 'all') {
        $ag_id = (int)$ag_office_id;
        $stmt->bind_param('iisi', $year, $month, $type, $ag_id);
    } else {
        $stmt->bind_param('iis', $year, $month, $type);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[$row['category']] = $row;
    }

    // Get estimated beneficiaries (max per year per office)
    $sql_est = "SELECT category, SUM(max_est) as est FROM (SELECT category, ag_office_id, MAX(estimated_beneficiaries) as max_est FROM assistance_records WHERE year = ? AND assistance_type = ? $office_filter GROUP BY category, ag_office_id) as t GROUP BY category";
    $stmt_est = $conn->prepare($sql_est);
    if ($ag_office_id !== 'all') {
        $stmt_est->bind_param('isi', $year, $type, $ag_id);
    } else {
        $stmt_est->bind_param('is', $year, $type);
    }
    $stmt_est->execute();
    $result_est = $stmt_est->get_result();
    
    while ($row = $result_est->fetch_assoc()) {
        $cat = $row['category'];
        if (!isset($records[$cat])) {
            $records[$cat] = ['category' => $cat, 'act' => 0, 'amt' => 0];
        }
        $records[$cat]['est'] = $row['est'];
    }

    // Calculate cumulative amount up to this month for the given year for all offices
    $sql_cum = "SELECT category, SUM(amount) as cumulative_amount, SUM(actual_beneficiaries) as cumulative_act FROM assistance_records WHERE year = ? AND month <= ? AND assistance_type = ? $office_filter GROUP BY category";
    $stmt_cum = $conn->prepare($sql_cum);
    if ($ag_office_id !== 'all') {
        $ag_id = (int)$ag_office_id;
        $stmt_cum->bind_param('iisi', $year, $month, $type, $ag_id);
    } else {
        $stmt_cum->bind_param('iis', $year, $month, $type);
    }
    $stmt_cum->execute();
    $result_cum = $stmt_cum->get_result();
    
    $cumulative = [];
    while ($row = $result_cum->fetch_assoc()) {
        $cumulative[$row['category']] = [
            'amount' => $row['cumulative_amount'],
            'act' => $row['cumulative_act']
        ];
    }

    foreach ($records as $cat => &$rec) {
        $rec['cumulative_amount'] = $cumulative[$cat]['amount'] ?? 0;
        $rec['cumulative_act'] = $cumulative[$cat]['act'] ?? 0;
    }

    echo json_encode(['records' => $records, 'cumulative' => $cumulative]);
    exit;
}

if ($action == 'save_records') {
    session_start();
    $user_role = $_SESSION['role'] ?? null;
    if (!$user_role) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }

    $ag_office_id = (int)$data['ag_office_id'];
    $year = (int)$data['year'];
    $month = (int)$data['month'];
    $type = $data['type'];
    $allocated_amount = (float)($data['allocated_amount'] ?? 0);
    $records = $data['records']; // Array of associative arrays

    $conn->begin_transaction();
    try {
        if ($user_role === 'superadmin' || $user_role === 'admin') {
            // Save allocated amount
            $stmt_alloc = $conn->prepare("INSERT INTO allocations (ag_office_id, year, assistance_type, allocated_amount) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE allocated_amount = VALUES(allocated_amount)");
            $stmt_alloc->bind_param('iisd', $ag_office_id, $year, $type, $allocated_amount);
            $stmt_alloc->execute();
        }

        $max_allocs = [];
        $max_ests = [];
        if ($user_role !== 'superadmin') {
            $sql_est = "SELECT category, MAX(estimated_beneficiaries) as max_est, MAX(allocated_amount) as max_alloc FROM assistance_records WHERE ag_office_id = ? AND year = ? AND assistance_type = ? GROUP BY category";
            $stmt_est = $conn->prepare($sql_est);
            $stmt_est->bind_param('iis', $ag_office_id, $year, $type);
            $stmt_est->execute();
            $result_est = $stmt_est->get_result();
            while ($row = $result_est->fetch_assoc()) {
                $max_allocs[$row['category']] = $row['max_alloc'];
                $max_ests[$row['category']] = $row['max_est'];
            }
        }

        if ($user_role === 'superadmin') {
            $stmt = $conn->prepare("INSERT INTO assistance_records (ag_office_id, year, month, assistance_type, category, estimated_beneficiaries, actual_beneficiaries, amount, allocated_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE estimated_beneficiaries = VALUES(estimated_beneficiaries), actual_beneficiaries = VALUES(actual_beneficiaries), amount = VALUES(amount), allocated_amount = VALUES(allocated_amount)");
        } elseif ($user_role === 'admin') {
            $stmt = $conn->prepare("INSERT INTO assistance_records (ag_office_id, year, month, assistance_type, category, estimated_beneficiaries, actual_beneficiaries, amount, allocated_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE actual_beneficiaries = VALUES(actual_beneficiaries), amount = VALUES(amount), allocated_amount = VALUES(allocated_amount)");
        } else {
            $stmt = $conn->prepare("INSERT INTO assistance_records (ag_office_id, year, month, assistance_type, category, estimated_beneficiaries, actual_beneficiaries, amount, allocated_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE actual_beneficiaries = VALUES(actual_beneficiaries), amount = VALUES(amount)");
        }

        foreach ($records as $rec) {
            $cat = $rec['category'];
            $est = (int)($rec['estimated'] ?? 0);
            $act = (int)($rec['actual'] ?? 0);
            $amt = (float)($rec['amount'] ?? 0);
            $alloc_cat = (float)($rec['allocated_amount'] ?? 0);

            if ($user_role !== 'superadmin') {
                $est = (int)($max_ests[$cat] ?? 0);
            }
            if ($user_role === 'user') {
                $alloc_cat = (float)($max_allocs[$cat] ?? 0);
            }

            $stmt->bind_param('iiissiidd', $ag_office_id, $year, $month, $type, $cat, $est, $act, $amt, $alloc_cat);
            $stmt->execute();
        }
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action == 'get_ag_summary') {
    $year = (int)($_GET['year'] ?? date('Y'));
    $month = (int)($_GET['month'] ?? date('n'));
    $type = $_GET['type'] ?? 'financial';
    $category = $_GET['category'] ?? 'all';

    $cat_filter = ($category !== 'all') ? " AND r.category = ?" : "";
    $cat_filter_est = ($category !== 'all') ? " AND category = ?" : "";
    
    $sql = "SELECT 
                a.id as office_id, 
                a.district,
                a.name as office_name,
                SUM(r.actual_beneficiaries) as act, 
                SUM(r.amount) as amt,
                SUM(r.allocated_amount) as alloc
            FROM ag_offices a
            LEFT JOIN assistance_records r ON a.id = r.ag_office_id AND r.year = ? AND r.month = ? AND r.assistance_type = ? $cat_filter
            GROUP BY a.id, a.district, a.name
            ORDER BY a.district, a.name";
    
    $stmt = $conn->prepare($sql);
    if ($category !== 'all') {
        $stmt->bind_param('iiss', $year, $month, $type, $category);
    } else {
        $stmt->bind_param('iis', $year, $month, $type);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[$row['office_id']] = $row;
        $records[$row['office_id']]['est'] = 0; // default
    }

    // Get estimated beneficiaries
    $sql_est = "SELECT ag_office_id as office_id, SUM(max_est) as est FROM (SELECT ag_office_id, category, MAX(estimated_beneficiaries) as max_est FROM assistance_records WHERE year = ? AND assistance_type = ? $cat_filter_est GROUP BY ag_office_id, category) as t GROUP BY ag_office_id";
    $stmt_est = $conn->prepare($sql_est);
    if ($category !== 'all') {
        $stmt_est->bind_param('iss', $year, $type, $category);
    } else {
        $stmt_est->bind_param('is', $year, $type);
    }
    $stmt_est->execute();
    $result_est = $stmt_est->get_result();
    
    while ($row = $result_est->fetch_assoc()) {
        if (isset($records[$row['office_id']])) {
            $records[$row['office_id']]['est'] = $row['est'];
        }
    }

    $sql_cum = "SELECT 
                    a.id as office_id, 
                    SUM(r.amount) as cumulative_amount,
                    SUM(r.actual_beneficiaries) as cumulative_act
                FROM ag_offices a
                LEFT JOIN assistance_records r ON a.id = r.ag_office_id AND r.year = ? AND r.month <= ? AND r.assistance_type = ? $cat_filter
                GROUP BY a.id";
    $stmt_cum = $conn->prepare($sql_cum);
    if ($category !== 'all') {
        $stmt_cum->bind_param('iiss', $year, $month, $type, $category);
    } else {
        $stmt_cum->bind_param('iis', $year, $month, $type);
    }
    $stmt_cum->execute();
    $result_cum = $stmt_cum->get_result();
    
    $cumulative = [];
    while ($row = $result_cum->fetch_assoc()) {
        $cumulative[$row['office_id']] = [
            'amount' => $row['cumulative_amount'],
            'act' => $row['cumulative_act']
        ];
    }

    foreach ($records as $id => &$rec) {
        $rec['cumulative_amount'] = $cumulative[$id]['amount'] ?? 0;
        $rec['cumulative_act'] = $cumulative[$id]['act'] ?? 0;
    }

    echo json_encode(array_values($records));
    exit;
}

if ($action == 'clear_selected_records') {
    session_start();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }

    $ag_office_id = (int)$data['ag_office_id'];
    $year = (int)$data['year'];
    $month = (int)$data['month'];
    $type = $data['type'];

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("DELETE FROM assistance_records WHERE ag_office_id = ? AND year = ? AND month = ? AND assistance_type = ?");
        $stmt->bind_param('iiis', $ag_office_id, $year, $month, $type);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action == 'clear_category_records') {
    session_start();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }

    $ag_office_id = (int)$data['ag_office_id'];
    $year = (int)$data['year'];
    $month = (int)$data['month'];
    $type = $data['type'];
    $category = $data['category'];

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("DELETE FROM assistance_records WHERE ag_office_id = ? AND year = ? AND month = ? AND assistance_type = ? AND category = ?");
        $stmt->bind_param('iiiss', $ag_office_id, $year, $month, $type, $category);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action == 'update_estimate') {
    session_start();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }

    $ag_office_id = (int)$data['ag_office_id'];
    $year = (int)$data['year'];
    $month = (int)$data['month'];
    $type = $data['type'];
    $category = $data['category'];
    $estimate = (int)$data['estimate'];

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO assistance_records (ag_office_id, year, month, assistance_type, category, estimated_beneficiaries) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE estimated_beneficiaries = VALUES(estimated_beneficiaries)");
        $stmt->bind_param('iiissi', $ag_office_id, $year, $month, $type, $category, $estimate);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action == 'get_alloc_summary') {
    session_start();
    $user_role = $_SESSION['role'] ?? null;
    if ($user_role !== 'superadmin' && $user_role !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $year = (int)($_GET['year'] ?? date('Y'));
    $month = (int)($_GET['month'] ?? date('n'));
    $type = $_GET['type'] ?? 'financial';

    // Categories list for the type
    $cats = [
        'financial' => [
            'මහජනාධාර', 'පිලිකාධාර', 'තැලසීමියාධාර', 'ක්ෂය රෝගාධාර', 
            'ලාදුරු ආධාර', 'සිසුමිණ ශිෂ්‍යාධාර', 'විශේෂ වෛද්‍යාධාර', 'වකුගඩු ආධාර'
        ],
        'equipment' => [
            'වන අලි වගා හානි සඳහා ගෙවීම් සිදු කිරීම', 'නිවාස හානි සඳහා ගෙවීම් සිදු කිරීම', 
            'රෝද පුටු ලබාදීම', 'ත්‍රෛසිකල් ලබාදීම', 'කිහිලිකරු ලබාදීම', 'අවිදින රාමු ලබාදීම', 
            'අත්වාරු ලබාදීම', 'වායුමෙට්ට/ජල මෙට්ට ලබාදීම', 'ශ්‍රවණ උපකරණ ලබාදීම', 
            'කෘතිම අත් ලබාදීම', 'කෘතිම පාද ලබාදීම', 'සුදුසැරයටි ලබාදීම', 'ඇස් කණ්ණාඩි ලබාදීම', 
            'පිළිසරණී නිවාස ආධාර ලබාදීම', 'ස්වශක්ති ආධාර ලබාදීම', 'ස්වයංරැකියා ආධාර ලබාදීම'
        ]
    ];

    $records = [];
    foreach ($cats[$type] as $cat) {
        $records[$cat] = [
            'category' => $cat,
            'allocated_amount' => 0.00,
            'cumulative_spent' => 0.00,
            'remaining_balance' => 0.00
        ];
    }

    // 1. Get global allocations for the province for the entire year
    $sql_alloc = "SELECT category, allocated_amount FROM global_category_allocations WHERE year = ? AND assistance_type = ?";
    $stmt_alloc = $conn->prepare($sql_alloc);
    $stmt_alloc->bind_param('is', $year, $type);
    $stmt_alloc->execute();
    $res_alloc = $stmt_alloc->get_result();
    while ($row = $res_alloc->fetch_assoc()) {
        $cat = $row['category'];
        if (isset($records[$cat])) {
            $records[$cat]['allocated_amount'] = (float)$row['allocated_amount'];
        }
    }

    // 2. Get cumulative spent across all offices up to the selected month
    $sql_spent = "SELECT category, SUM(amount) as total_spent FROM assistance_records WHERE year = ? AND month <= ? AND assistance_type = ? GROUP BY category";
    $stmt_spent = $conn->prepare($sql_spent);
    $stmt_spent->bind_param('iis', $year, $month, $type);
    $stmt_spent->execute();
    $res_spent = $stmt_spent->get_result();
    while ($row = $res_spent->fetch_assoc()) {
        $cat = $row['category'];
        if (isset($records[$cat])) {
            $records[$cat]['cumulative_spent'] = (float)$row['total_spent'];
        }
    }

    // Calculate remaining balance
    foreach ($records as &$rec) {
        $rec['remaining_balance'] = $rec['allocated_amount'] - $rec['cumulative_spent'];
    }

    echo json_encode(array_values($records));
    exit;
}

if ($action == 'update_allocation') {
    session_start();
    $user_role = $_SESSION['role'] ?? null;
    if ($user_role !== 'superadmin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }

    $year = (int)$data['year'];
    $type = $data['type'];
    $category = $data['category'];
    $allocated_amount = (float)$data['allocated_amount'];

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO global_category_allocations (year, assistance_type, category, allocated_amount) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE allocated_amount = VALUES(allocated_amount)");
        $stmt->bind_param('issd', $year, $type, $category, $allocated_amount);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['error' => 'Invalid action']);
