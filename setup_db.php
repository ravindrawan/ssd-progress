<?php
$conn = new mysqli('124.43.163.151', 'root', 'Ravi@2025');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

$conn->query('CREATE DATABASE IF NOT EXISTS social_services_monthly CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$conn->select_db('social_services_monthly');

$sql = 'CREATE TABLE IF NOT EXISTS ag_offices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    district VARCHAR(50),
    name VARCHAR(255) NOT NULL
)';
$conn->query($sql);

$sql = 'CREATE TABLE IF NOT EXISTS assistance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ag_office_id INT NOT NULL,
    year INT NOT NULL,
    month INT NOT NULL,
    assistance_type ENUM("financial", "equipment") NOT NULL,
    category VARCHAR(255) NOT NULL,
    estimated_beneficiaries INT DEFAULT 0,
    actual_beneficiaries INT DEFAULT 0,
    amount DECIMAL(15,2) DEFAULT 0.00,
    allocated_amount DECIMAL(15,2) DEFAULT 0.00,
    UNIQUE KEY unique_record (ag_office_id, year, month, assistance_type, category),
    FOREIGN KEY (ag_office_id) REFERENCES ag_offices(id)
)';
$conn->query($sql);

$sql = 'CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    plain_password VARCHAR(255) DEFAULT NULL,
    role ENUM("superadmin", "admin", "user") NOT NULL
)';
$conn->query($sql);

$sql = 'CREATE TABLE IF NOT EXISTS allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ag_office_id INT NOT NULL,
    year INT NOT NULL,
    assistance_type ENUM("financial", "equipment") NOT NULL,
    allocated_amount DECIMAL(15,2) DEFAULT 0.00,
    UNIQUE KEY unique_alloc (ag_office_id, year, assistance_type),
    FOREIGN KEY (ag_office_id) REFERENCES ag_offices(id)
)';
$conn->query($sql);

$result = $conn->query('SELECT COUNT(*) as cnt FROM users');
$row = $result->fetch_assoc();
if ($row['cnt'] == 0) {
    // Default passwords are same as username
    $pwd_superadmin = password_hash('superadmin', PASSWORD_DEFAULT);
    $pwd_admin = password_hash('admin', PASSWORD_DEFAULT);
    $pwd_user = password_hash('user', PASSWORD_DEFAULT);
    
    $conn->query("INSERT INTO users (username, password, plain_password, role) VALUES ('superadmin', '$pwd_superadmin', 'superadmin', 'superadmin')");
    $conn->query("INSERT INTO users (username, password, plain_password, role) VALUES ('admin', '$pwd_admin', 'admin', 'admin')");
    $conn->query("INSERT INTO users (username, password, plain_password, role) VALUES ('user', '$pwd_user', 'user', 'user')");
}

$result = $conn->query('SELECT COUNT(*) as cnt FROM ag_offices');
$row = $result->fetch_assoc();
if ($row['cnt'] == 0) {
    // Insert Wayamba 46 DS Offices
    $kurunegala = [
        'අලව්ව (Alawwa)', 'අඹන්පොළ (Ambanpola)', 'බමුණාකොටුව (Bamunakotuwa)', 'බිංගිරිය (Bingiriya)', 'ඇහැටුවැව (Ehetuwewa)', 'ගල්ගමුව (Galgamuwa)', 'ගනේවත්ත (Ganewatta)', 'ගිරිබාව (Giribawa)', 'ඉබ්බාගමුව (Ibbagamuwa)', 'කොබෙයිගනේ (Kobeigane)', 'කොටවෙහෙර (Kotavehera)', 'කුලියාපිටිය නැගෙනහිර (Kuliyapitiya East)', 'කුලියාපිටිය බටහිර (Kuliyapitiya West)', 'කුරුණෑගල (Kurunegala)', 'මහව (Maho)', 'මල්ලවපිටිය (Mallawapitiya)', 'මස්පොත (Maspotha)', 'මාවතගම (Mawathagama)', 'නාරම්මල (Narammala)', 'නිකවැරටිය (Nikaweratiya)', 'පඬුවස්නුවර බටහිර (Panduwasnuwara West)', 'පන්නල (Pannala)', 'පොල්ගහවෙල (Polgahawela)', 'පොල්පිතිගම (Polpithigama)', 'රස්නායකපුර (Rasnayakapura)', 'රිදීගම (Rideegama)', 'උඩුබැද්දාව (Udubaddawa)', 'වාරියපොළ (Wariyapola)', 'වීරඹුගෙදර (Weerambugedera)', 'පඬුවස්නුවර නැගෙනහිර (Panduwasnuwara East)'
    ];
    $puttalam = [
        'ආණමඩුව (Anamaduwa)', 'ආරච්චිකට්ටුව (Arachchikattuwa)', 'හලාවත (Chilaw)', 'දංකොටුව (Dankotuwa)', 'කල්පිටිය (Kalpitiya)', 'කරුවලගස්වැව (Karuwalagaswewa)', 'මාදම්පේ (Madampe)', 'මහකුඹුක්කඩවල (Mahakumbukkadawala)', 'මහවැව (Mahawewa)', 'මුන්දලම (Mundel)', 'නාත්තන්ඩිය (Nattandiya)', 'නවගත්තේගම (Nawagattegama)', 'පල්ලම (Pallama)', 'පුත්තලම (Puttalam)', 'වනතවිල්ලුව (Vanathavilluwa)', 'වෙන්නප්පුව (Wennappuwa)'
    ];

    foreach ($kurunegala as $ds) {
        $stmt = $conn->prepare("INSERT INTO ag_offices (district, name) VALUES ('Kurunegala', ?)");
        $stmt->bind_param("s", $ds);
        $stmt->execute();
    }
    foreach ($puttalam as $ds) {
        $stmt = $conn->prepare("INSERT INTO ag_offices (district, name) VALUES ('Puttalam', ?)");
        $stmt->bind_param("s", $ds);
        $stmt->execute();
    }
}
echo 'DB setup done.';
