<?php
require 'auth.php';
require_login();
require 'db.php';

header('Content-Type: application/json; charset=utf-8');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$query = isset($input['query']) ? trim($input['query']) : '';

if (empty($query)) {
    echo json_encode([
        'reply' => '<p>කරුණාකර යම් ප්‍රශ්නයක් ඇතුළත් කරන්න. (Please enter a question.)</p>',
        'suggested_chips' => []
    ]);
    exit;
}

// 1. Fetch all AG offices for matching
$offices_res = $conn->query("SELECT id, name, district FROM ag_offices");
$offices = [];
while ($row = $offices_res->fetch_assoc()) {
    $offices[] = $row;
}

// Helper functions for entity extraction
function detect_office($query, $offices) {
    foreach ($offices as $office) {
        $name = $office['name'];
        $sinhala = '';
        $english = '';
        if (preg_match('/^([^\(]+)\(([^)]+)\)/u', $name, $matches)) {
            $sinhala = trim($matches[1]);
            $english = trim($matches[2]);
        } else {
            $sinhala = $name;
        }

        if (($english !== '' && stripos($query, $english) !== false) || 
            (stripos($query, $sinhala) !== false)) {
            return $office;
        }
    }
    return null;
}

function detect_category($query) {
    $mapping = [
        'මහජනාධාර' => ['මහජනාධාර', 'මහජන ආධාර', 'mahajana', 'public aid'],
        'පිලිකාධාර' => ['පිලිකා', 'cancer', 'pilika'],
        'තැලසීමියාධාර' => ['තැලසීමියා', 'thalassemia', 'talasimiya'],
        'ක්ෂය රෝගාධාර' => ['ක්ෂය', 'tb', 'tuberculosis', 'kshaya'],
        'ලාදුරු ආධාර' => ['ලාදුරු', 'leprosy', 'laduru'],
        'සිසුමිණ ශිෂ්‍යාධාර' => ['සිසුමිණ', 'sisumina', 'scholarship'],
        'විශේෂ වෛද්‍යාධාර' => ['වෛද්‍ය', 'medical', 'waidya'],
        'වකුගඩු ආධාර' => ['වකුගඩු', 'kidney', 'renal', 'wakugadu'],
        'වන අලි වගා හානි සඳහා ගෙවීම් සිදු කිරීම' => ['වන අලි', 'වගා හානි', 'elephant', 'ali'],
        'නිවාස හානි සඳහා ගෙවීම් සිදු කිරීම' => ['නිවාස හානි', 'house damage', 'niwasa hani'],
        'රෝද පුටු ලබාදීම' => ['රෝද පුටු', 'wheelchair', 'roda putu'],
        'ත්‍රෛසිකල් ලබාදීම' => ['ත්‍රෛසිකල්', 'tricycle', 'thraisikal'],
        'කිහිලිකරු ලබාදීම' => ['කිහිලිකරු', 'crutches', 'kihilikaru'],
        'අවිදින රාමු ලබාදීම' => ['රාමු', 'walking frame', 'awidina ramu'],
        'අත්වාරු ලබාදීම' => ['අත්වාරු', 'crutches', 'athwaru'],
        'වායුමෙට්ට/ජල මෙට්ට ලබාදීම' => ['මෙට්ට', 'mattress', 'metta'],
        'ශ්‍රවණ උපකරණ ලබාදීම' => ['ශ්‍රවණ', 'hearing', 'shrawana'],
        'කෘතිම අත් ලබාදීම' => ['කෘතිම අත්', 'prosthetic hand', 'kruthima ath'],
        'කෘතිම පාද ලබාදීම' => ['කෘතිම පාද', 'prosthetic leg', 'kruthima pada'],
        'සුදුසැරයටි ලබාදීම' => ['සුදුසැරයටි', 'white cane', 'sudu sarayati'],
        'ඇස් කණ්ණාඩි ලබාදීම' => ['ඇස් කණ්ණාඩි', 'spectacles', 'as kannadi', 'glasses'],
        'පිළිසරණී නිවාස ආධාර ලබාදීම' => ['පිළිසරණී', 'pilisarani'],
        'ස්වශක්ති ආධාර ලබාදීම' => ['ස්වශක්ති', 'swashakthi'],
        'ස්වයංරැකියා ආධාර ලබාදීම' => ['රැකියා', 'self employment', 'swayan rakiya']
    ];

    foreach ($mapping as $category => $keywords) {
        foreach ($keywords as $kw) {
            if (stripos($query, $kw) !== false) {
                return $category;
            }
        }
    }
    return null;
}

function detect_year($query) {
    if (preg_match('/\b(20\d{2})\b/', $query, $matches)) {
        return (int)$matches[1];
    }
    return null;
}

function detect_month($query) {
    $months = [
        1 => ['ජනවාරි', 'january', 'jan', ' 1 '],
        2 => ['පෙබරවාරි', 'february', 'feb', ' 2 '],
        3 => ['මාර්තු', 'march', 'mar', ' 3 '],
        4 => ['අප්‍රේල්', 'april', 'apr', ' 4 '],
        5 => ['මැයි', 'may', ' 5 '],
        6 => ['ජූනි', 'june', 'jun', ' 6 '],
        7 => ['ජූලි', 'july', 'jul', ' 7 '],
        8 => ['අගෝස්තු', 'august', 'aug', ' 8 '],
        9 => ['සැප්තැම්බර්', 'september', 'sep', ' 9 '],
        10 => ['ඔක්තෝබර්', 'october', 'oct', '10'],
        11 => ['නොවැම්බර්', 'november', 'nov', '11'],
        12 => ['දෙසැම්බර්', 'december', 'dec', '12']
    ];

    foreach ($months as $num => $keywords) {
        foreach ($keywords as $kw) {
            if (stripos($query, $kw) !== false) {
                return $num;
            }
        }
    }
    return null;
}

// 2. Extract Entities
$office = detect_office($query, $offices);
$category = detect_category($query);
$year = detect_year($query);
$month = detect_month($query);

// Set defaults if context warrants
if (!$year) {
    // If not specified, default to 2026 as this is the primary database year
    $year = 2026;
}

$monthNames = [
    1 => "ජනවාරි", 2 => "පෙබරවාරි", 3 => "මාර්තු", 4 => "අප්‍රේල්", 
    5 => "මැයි", 6 => "ජූනි", 7 => "ජූලි", 8 => "අගෝස්තු", 
    9 => "සැප්තැම්බර්", 10 => "ඔක්තෝබර්", 11 => "නොවැම්බර්", 12 => "දෙසැම්බර්"
];

// Normalize query for intent detection
$normQuery = mb_strtolower($query, 'UTF-8');

$reply = "";
$suggested_chips = [];

// Determine user intent

// A. Help / FAQ intent
if (stripos($normQuery, 'help') !== false || stripos($normQuery, 'උදව්') !== false || stripos($normQuery, 'කොහොමද') !== false || stripos($normQuery, 'instructions') !== false || stripos($normQuery, 'hello') !== false || stripos($normQuery, 'hi') !== false || stripos($normQuery, 'හලෝ') !== false) {
    $reply = "
    <div class='chat-message-group'>
        <p>👋 ආයුබෝවන්! මම <strong>සමාජ සේවා මාසික ප්‍රගති සහයක</strong>.</p>
        <p>පහත දැක්වෙන දේවල් මගෙන් විමසන්න පුළුවන්:</p>
        <ul style='margin-left: 15px; margin-top: 5px; list-style-type: disc;'>
            <li><strong>වියදම් සහ ප්‍රතිලාභීන්:</strong> <em>'2026 අලව්ව මුළු වියදම කීයද?'</em> හෝ <em>'මහජනාධාර ප්‍රතිලාභීන් ගණන'</em></li>
            <li><strong>වැඩිම ක්‍රියාකාරීත්වය:</strong> <em>'වැඩිම වියදමක් කළ කාර්යාලය'</em> හෝ <em>'වැඩිම වකුගඩු ආධාර ලබාදුන් කාර්යාලය'</em></li>
            <li><strong>ප්‍රතිපාදන:</strong> <em>'හලාවත 2026 ප්‍රතිපාදන ප්‍රමාණය'</em></li>
            <li><strong>කාර්යාලීය තොරතුරු:</strong> <em>'Alawwa summary'</em></li>
        </ul>
    </div>";
    
    $suggested_chips = [
        "🏆 වැඩිම වියදම් කළ කාර්යාලය",
        "📊 2026 සමස්ත ප්‍රගතිය",
        "📍 ප්‍රාදේශීය ලේකම් කාර්යාල",
        "💡 ආධාර කාණ්ඩ ලැයිස්තුව"
    ];
}
// B. Category list intent
elseif (stripos($normQuery, 'කාණ්ඩ') !== false || stripos($normQuery, 'categories') !== false || stripos($normQuery, 'වර්ග') !== false || stripos($normQuery, 'ලැයිස්තුව') !== false && stripos($normQuery, 'ආධාර') !== false) {
    $reply = "
    <div class='chat-message-group'>
        <p>📋 <strong>පද්ධතියේ ඇති ආධාර කාණ්ඩ ලැයිස්තුව:</strong></p>
        <div style='margin-top: 8px;'>
            <strong>💰 මූල්‍යාධාර (Financial):</strong> මහජනාධාර, පිලිකාධාර, තැලසීමියාධාර, ක්ෂය රෝගාධාර, ලාදුරු ආධාර, සිසුමිණ ශිෂ්‍යාධාර, විශේෂ වෛද්‍යාධාර, වකුගඩු ආධාර.
        </div>
        <div style='margin-top: 8px;'>
            <strong>♿ ආබාධිත උපකරණ සහ වෙනත් (Equipment):</strong> වන අලි වගා හානි, නිවාස හානි, රෝද පුටු, ත්‍රෛසිකල්, කිහිලිකරු, අවිදින රාමු, අත්වාරු, වායුමෙට්ට, ශ්‍රවණ උපකරණ, කෘතිම අත්/පාද, සුදුසැරයටි, ඇස් කණ්ණාඩි, පිළිසරණී නිවාස, ස්වශක්ති, ස්වයංරැකියා.
        </div>
    </div>";
    
    $suggested_chips = [
        "📊 2026 සමස්ත ප්‍රගතිය",
        "🏆 වැඩිම වියදම් කළ කාර්යාලය"
    ];
}
// C. Office list intent
elseif (stripos($normQuery, 'කාර්යාල') !== false || stripos($normQuery, 'offices') !== false || stripos($normQuery, 'district') !== false) {
    $kurunegala_count = 0;
    $puttalam_count = 0;
    foreach ($offices as $o) {
        if ($o['district'] === 'Kurunegala') $kurunegala_count++;
        if ($o['district'] === 'Puttalam') $puttalam_count++;
    }
    
    $reply = "
    <div class='chat-message-group'>
        <p>📍 <strong>වයඹ පළාතේ ප්‍රාදේශීය ලේකම් කාර්යාල (DS Offices):</strong></p>
        <p>පද්ධතිය තුළ මුළු කාර්යාල <strong>" . count($offices) . "</strong> ක් ලියාපදිංචි වී ඇත.</p>
        <ul style='margin-left: 15px; margin-top: 5px; list-style-type: circle;'>
            <li>කුරුණෑගල දිස්ත්‍රික්කය: <strong>{$kurunegala_count}</strong></li>
            <li>පුත්තලම දිස්ත්‍රික්කය: <strong>{$puttalam_count}</strong></li>
        </ul>
        <p style='font-size: 12px; color: #94a3b8; margin-top: 5px;'>ඕනෑම කාර්යාලයක සාරාංශයක් බැලීමට <em>'[කාර්යාලයේ නම] summary'</em> ලෙස විමසන්න. (උදා: Alawwa summary)</p>
    </div>";
    
    $suggested_chips = [
        "🏆 වැඩිම වියදම් කළ කාර්යාලය",
        "📊 2026 සමස්ත ප්‍රගතිය",
        "💡 ආධාර කාණ්ඩ ලැයිස්තුව"
    ];
}
// D. Top Performer / Highest intent
elseif (stripos($normQuery, 'වැඩිම') !== false || stripos($normQuery, 'top') !== false || stripos($normQuery, 'highest') !== false || stripos($normQuery, 'best') !== false) {
    $is_beneficiaries = (stripos($normQuery, 'ප්‍රතිලාභීන්') !== false || stripos($normQuery, 'beneficiaries') !== false || stripos($normQuery, 'ගණන') !== false || stripos($normQuery, 'count') !== false);
    
    $select_col = $is_beneficiaries ? "SUM(r.actual_beneficiaries) as val" : "SUM(r.amount) as val";
    $val_label = $is_beneficiaries ? "ප්‍රතිලාභීන් ගණන" : "වියදම (රු.)";
    
    $sql = "SELECT a.name as office_name, a.district, $select_col 
            FROM ag_offices a 
            JOIN assistance_records r ON a.id = r.ag_office_id";
    
    $where = [];
    $params = [];
    $types = '';
    
    if ($year) { $where[] = "r.year = ?"; $params[] = $year; $types .= 'i'; }
    if ($month) { $where[] = "r.month = ?"; $params[] = $month; $types .= 'i'; }
    if ($category) { $where[] = "r.category = ?"; $params[] = $category; $types .= 's'; }
    
    if ($where) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    
    $sql .= " GROUP BY a.id ORDER BY val DESC LIMIT 3";
    
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $context_str = "වර්ෂ {$year}";
    if ($month) {
        $context_str .= " " . $monthNames[$month];
    }
    if ($category) {
        $context_str .= " [{$category}] සඳහා";
    }
    
    if ($result->num_rows > 0) {
        $reply = "
        <div class='chat-message-group'>
            <p>🏆 <strong>වැඩිම {$val_label}ක් සහිත ප්‍රමුඛ කාර්යාල (Top 3 Offices):</strong></p>
            <p style='font-size: 13px; color: #a5b4fc;'>({$context_str})</p>
            <table style='width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 13px;'>
                <thead>
                    <tr style='border-bottom: 1px solid rgba(255,255,255,0.1); text-align: left;'>
                        <th style='padding: 6px 0; color: #94a3b8;'>කාර්යාලය</th>
                        <th style='padding: 6px 0; color: #94a3b8; text-align: right;'>{$val_label}</th>
                    </tr>
                </thead>
                <tbody>";
        
        $rank = 1;
        while ($row = $result->fetch_assoc()) {
            $formatted_val = $is_beneficiaries ? number_format($row['val']) : "රු. " . number_format($row['val'], 2);
            $emoji = ($rank == 1) ? "🥇" : (($rank == 2) ? "🥈" : "🥉");
            $reply .= "
                    <tr style='border-bottom: 1px solid rgba(255,255,255,0.05);'>
                        <td style='padding: 6px 0;'>{$emoji} {$row['office_name']} ({$row['district']})</td>
                        <td style='padding: 6px 0; text-align: right; font-weight: 600; color: #fbbf24;'>{$formatted_val}</td>
                    </tr>";
            $rank++;
        }
        
        $reply .= "
                </tbody>
            </table>
        </div>";
    } else {
        $reply = "<p>🔍 {$context_str} කිසිදු දත්තයක් හමු නොවීය.</p>";
    }
    
    $suggested_chips = [
        "📊 2026 සමස්ත ප්‍රගතිය",
        "💡 ආධාර කාණ්ඩ ලැයිස්තුව"
    ];
}
// E. Allocations, Budget and Remaining intent
elseif (stripos($normQuery, 'ප්‍රතිපාදන') !== false || stripos($normQuery, 'allocation') !== false || stripos($normQuery, 'budget') !== false || stripos($normQuery, 'ශේෂය') !== false || stripos($normQuery, 'balance') !== false || stripos($normQuery, 'ඉතිරිය') !== false) {
    // 1. Get allocated amount
    $sql_alloc = "SELECT SUM(allocated_amount) as total_alloc FROM allocations";
    $where = [];
    $params = [];
    $types = '';
    
    if ($office) { $where[] = "ag_office_id = ?"; $params[] = $office['id']; $types .= 'i'; }
    if ($year) { $where[] = "year = ?"; $params[] = $year; $types .= 'i'; }
    if ($where) { $sql_alloc .= " WHERE " . implode(" AND ", $where); }
    
    $stmt_alloc = $conn->prepare($sql_alloc);
    if ($params) { $stmt_alloc->bind_param($types, ...$params); }
    $stmt_alloc->execute();
    $alloc_row = $stmt_alloc->get_result()->fetch_assoc();
    $total_alloc = (float)($alloc_row['total_alloc'] ?? 0);
    
    // 2. Get actual spent
    $sql_spent = "SELECT SUM(amount) as total_spent FROM assistance_records";
    $where_s = [];
    $params_s = [];
    $types_s = '';
    if ($office) { $where_s[] = "ag_office_id = ?"; $params_s[] = $office['id']; $types_s .= 'i'; }
    if ($year) { $where_s[] = "year = ?"; $params_s[] = $year; $types_s .= 'i'; }
    if ($month) { $where_s[] = "month <= ?"; $params_s[] = $month; $types_s .= 'i'; } // Cumulative up to month
    if ($where_s) { $sql_spent .= " WHERE " . implode(" AND ", $where_s); }
    
    $stmt_spent = $conn->prepare($sql_spent);
    if ($params_s) { $stmt_spent->bind_param($types_s, ...$params_s); }
    $stmt_spent->execute();
    $spent_row = $stmt_spent->get_result()->fetch_assoc();
    $total_spent = (float)($spent_row['total_spent'] ?? 0);
    
    $remaining = $total_alloc - $total_spent;
    
    $target_name = $office ? $office['name'] : "සියලුම ප්‍රා.ලේ කාර්යාල";
    $time_context = "වර්ෂ {$year}" . ($month ? " {$monthNames[$month]} දක්වා" : "");
    
    $reply = "
    <div class='chat-message-group'>
        <p>💰 <strong>ප්‍රතිපාදන සහ වියදම් විශ්ලේෂණය:</strong></p>
        <p style='font-size: 12px; color: #cbd5e1; margin-bottom: 8px;'>🎯 {$target_name} ({$time_context})</p>
        <div style='background: rgba(255,255,255,0.03); border-radius: 8px; padding: 10px; border: 1px solid rgba(255,255,255,0.05);'>
            <div style='display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 13px;'>
                <span>වෙන්කළ ප්‍රතිපාදන (Allocation):</span>
                <span style='font-weight: 600; color: #a5b4fc;'>රු. " . number_format($total_alloc, 2) . "</span>
            </div>
            <div style='display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 13px;'>
                <span>මේ දක්වා මුළු වියදම (Spent):</span>
                <span style='font-weight: 600; color: #fca5a5;'>රු. " . number_format($total_spent, 2) . "</span>
            </div>
            <hr style='border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: 8px 0;'>
            <div style='display: flex; justify-content: space-between; font-weight: 600; font-size: 14px;'>
                <span>ඉතිරි ශේෂය (Remaining):</span>
                <span style='color: #86efac;'>රු. " . number_format($remaining, 2) . "</span>
            </div>
        </div>
    </div>";
    
    $suggested_chips = [
        "🏆 වැඩිම වියදම් කළ කාර්යාලය",
        "📊 2026 සමස්ත ප්‍රගතිය"
    ];
}
// F. Specific Office Summary or general query about an office
elseif ($office && (stripos($normQuery, 'summary') !== false || stripos($normQuery, 'සාරාංශය') !== false || stripos($normQuery, 'තොරතුරු') !== false || !$category)) {
    // Show a summary card for this office
    
    // Get allocations for this office
    $stmt_a = $conn->prepare("SELECT SUM(allocated_amount) as alloc FROM allocations WHERE ag_office_id = ? AND year = ?");
    $stmt_a->bind_param('ii', $office['id'], $year);
    $stmt_a->execute();
    $alloc_val = (float)($stmt_a->get_result()->fetch_assoc()['alloc'] ?? 0);
    
    // Get actual expenditure and actual beneficiaries
    $stmt_e = $conn->prepare("SELECT SUM(amount) as amt, SUM(actual_beneficiaries) as beneficiaries FROM assistance_records WHERE ag_office_id = ? AND year = ?");
    $stmt_e->bind_param('ii', $office['id'], $year);
    $stmt_e->execute();
    $spent_data = $stmt_e->get_result()->fetch_assoc();
    $spent_amt = (float)($spent_data['amt'] ?? 0);
    $total_ben = (int)($spent_data['beneficiaries'] ?? 0);
    
    // Get top categories for this office
    $stmt_top = $conn->prepare("SELECT category, SUM(amount) as amt FROM assistance_records WHERE ag_office_id = ? AND year = ? GROUP BY category ORDER BY amt DESC LIMIT 2");
    $stmt_top->bind_param('ii', $office['id'], $year);
    $stmt_top->execute();
    $top_res = $stmt_top->get_result();
    
    $top_cats = [];
    while ($r = $top_res->fetch_assoc()) {
        if ($r['amt'] > 0) {
            $top_cats[] = "{$r['category']} (රු. " . number_format($r['amt'], 0) . ")";
        }
    }
    $top_cats_str = count($top_cats) > 0 ? implode(", ", $top_cats) : "දත්ත නොමැත";
    
    $reply = "
    <div class='chat-message-group'>
        <p>🏢 <strong>🏢 ප්‍රාදේශීය ලේකම් කාර්යාලීය තොරතුරු:</strong></p>
        <p style='font-size: 15px; color: #fbbf24; font-weight: 600; margin-bottom: 8px;'>{$office['name']} ({$office['district']} දිස්ත්‍රික්කය)</p>
        <div style='background: rgba(255,255,255,0.03); border-radius: 8px; padding: 10px; border: 1px solid rgba(255,255,255,0.05); font-size: 13px;'>
            <div style='display: flex; justify-content: space-between; margin-bottom: 4px;'>
                <span>වර්ෂය:</span>
                <span style='font-weight: 600;'>{$year}</span>
            </div>
            <div style='display: flex; justify-content: space-between; margin-bottom: 4px;'>
                <span>වෙන්කළ මුළු ප්‍රතිපාදන:</span>
                <span style='font-weight: 600; color: #a5b4fc;'>රු. " . number_format($alloc_val, 2) . "</span>
            </div>
            <div style='display: flex; justify-content: space-between; margin-bottom: 4px;'>
                <span>මේ දක්වා මුළු වියදම:</span>
                <span style='font-weight: 600; color: #fca5a5;'>රු. " . number_format($spent_amt, 2) . "</span>
            </div>
            <div style='display: flex; justify-content: space-between; margin-bottom: 4px;'>
                <span>ඉතිරි ශේෂය (Balance):</span>
                <span style='font-weight: 600; color: #86efac;'>රු. " . number_format($alloc_val - $spent_amt, 2) . "</span>
            </div>
            <div style='display: flex; justify-content: space-between; margin-bottom: 4px;'>
                <span>මුළු ප්‍රතිලාභීන් සංඛ්‍යාව:</span>
                <span style='font-weight: 600; color: #38bdf8;'>{$total_ben}</span>
            </div>
            <div style='margin-top: 8px; font-size: 12px; color: #cbd5e1; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 6px;'>
                🎯 <strong>වැඩිම වියදම් කළ කාණ්ඩ:</strong> {$top_cats_str}
            </div>
        </div>
    </div>";
    
    $suggested_chips = [
        "🏆 වැඩිම වියදම් කළ කාර්යාලය",
        "📊 2026 සමස්ත ප්‍රගතිය"
    ];
}
// G. General query (Filter by Office, Category, Year, Month)
else {
    // Compile filter queries
    $is_beneficiaries = (stripos($normQuery, 'ප්‍රතිලාභීන්') !== false || stripos($normQuery, 'beneficiaries') !== false || stripos($normQuery, 'ගණන') !== false || stripos($normQuery, 'count') !== false);
    
    $select_col = $is_beneficiaries ? "SUM(r.actual_beneficiaries) as val" : "SUM(r.amount) as val";
    $val_label = $is_beneficiaries ? "ප්‍රතිලාභීන් ගණන" : "මුදල";
    
    $sql = "SELECT $select_col FROM assistance_records r";
    $where = [];
    $params = [];
    $types = '';
    
    if ($office) { $where[] = "r.ag_office_id = ?"; $params[] = $office['id']; $types .= 'i'; }
    if ($year) { $where[] = "r.year = ?"; $params[] = $year; $types .= 'i'; }
    if ($month) { $where[] = "r.month = ?"; $params[] = $month; $types .= 'i'; }
    if ($category) { $where[] = "r.category = ?"; $params[] = $category; $types .= 's'; }
    
    if ($where) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $value = $row ? (float)$row['val'] : 0.0;
    
    // Construct friendly Sinhala response context
    $context_parts = [];
    if ($office) $context_parts[] = "🏢 [{$office['name']}] කාර්යාලයේ";
    else $context_parts[] = "🏢 [සියලුම කාර්යාල] වල";
    
    if ($category) $context_parts[] = "🎯 [{$category}] කාණ්ඩය සඳහා";
    else $context_parts[] = "🎯 සියලුම ආධාර කාණ්ඩ වල";
    
    $time_str = "වර්ෂ {$year}";
    if ($month) $time_str .= " " . $monthNames[$month] . " මාසය";
    $context_parts[] = "📅 {$time_str}";
    
    $formatted_value = $is_beneficiaries ? number_format($value) . " ක්" : "රු. " . number_format($value, 2);
    
    $reply = "
    <div class='chat-message-group'>
        <p>🔍 <strong>දත්ත විමසුම් ප්‍රතිඵලය:</strong></p>
        <div style='font-size: 13px; color: #cbd5e1; margin-bottom: 8px; line-height: 1.4;'>
            " . implode("<br>", $context_parts) . "
        </div>
        <div style='background: rgba(16, 185, 129, 0.1); border-left: 4px solid #10b981; padding: 10px; border-radius: 0 8px 8px 0;'>
            <span style='font-size: 13px; color: #a7f3d0; display: block;'>මුළු {$val_label}:</span>
            <span style='font-size: 18px; font-weight: 700; color: #10b981;'>{$formatted_value}</span>
        </div>
    </div>";
    
    $suggested_chips = [
        "🏆 වැඩිම වියදම් කළ කාර්යාලය",
        "📊 2026 සමස්ත ප්‍රගතිය"
    ];
}

// Return JSON response
echo json_encode([
    'reply' => $reply,
    'suggested_chips' => $suggested_chips,
    'office_id' => $office ? (int)$office['id'] : null
]);
exit;
