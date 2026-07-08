<?php
// PHP CLI Integration Test for Allocation Summary API
$baseUrl = 'http://localhost/monthly%20progress';
$cookieFile = tempnam(sys_get_temp_dir(), 'cookie_');

// Helper to make cURL requests
function make_request($url, $method = 'GET', $data = null, $headers = []) {
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if (is_array($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $headers[] = 'Content-Type: application/json';
        }
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    return [
        'status' => $info['http_code'],
        'body' => $response,
        'redirect' => $info['redirect_url'] ?? null
    ];
}

echo "=== STEP 1: Logging in as superadmin ===\n";
$loginRes = make_request("$baseUrl/login.php", 'POST', [
    'username' => 'superadmin',
    'password' => 'superadmin'
]);
echo "Login Status: {$loginRes['status']}\n";
echo "Redirect to: {$loginRes['redirect']}\n\n";

echo "=== STEP 2: Fetching initial allocation summary ===\n";
$getRes = make_request("$baseUrl/api.php?action=get_alloc_summary&year=2026&month=6&type=financial");
echo "Get Summary Status: {$getRes['status']}\n";
$initialSummary = json_decode($getRes['body'], true);
if (!is_array($initialSummary)) {
    echo "Error: Failed to parse JSON response. Body: {$getRes['body']}\n";
    exit(1);
}

// Find initial value for මහජනාධාර
$mahajanaAlloc = 0;
foreach ($initialSummary as $item) {
    if ($item['category'] === 'මහජනාධාර') {
        $mahajanaAlloc = $item['allocated_amount'];
        break;
    }
}
echo "Current 'මහජනාධාර' allocation: $mahajanaAlloc\n\n";

echo "=== STEP 3: Updating 'මහජනාධාර' allocation to 789123.45 ===\n";
$updatePayload = json_encode([
    'year' => 2026,
    'type' => 'financial',
    'category' => 'මහජනාධාර',
    'allocated_amount' => 789123.45
]);
$updateRes = make_request("$baseUrl/api.php?action=update_allocation", 'POST', $updatePayload);
echo "Update Status: {$updateRes['status']}\n";
echo "Update Response: {$updateRes['body']}\n\n";

echo "=== STEP 4: Verifying updated allocation summary ===\n";
$getRes2 = make_request("$baseUrl/api.php?action=get_alloc_summary&year=2026&month=6&type=financial");
$updatedSummary = json_decode($getRes2['body'], true);
$newMahajanaAlloc = 0;
foreach ($updatedSummary as $item) {
    if ($item['category'] === 'මහජනාධාර') {
        $newMahajanaAlloc = $item['allocated_amount'];
        break;
    }
}
echo "New 'මහජනාධාර' allocation: $newMahajanaAlloc\n";
if ($newMahajanaAlloc == 789123.45) {
    echo "SUCCESS: Allocation successfully updated!\n\n";
} else {
    echo "FAILURE: Allocation did not match expected value!\n\n";
}

echo "=== STEP 5: Restoring original allocation ===\n";
$restorePayload = json_encode([
    'year' => 2026,
    'type' => 'financial',
    'category' => 'මහජනාධාර',
    'allocated_amount' => $mahajanaAlloc
]);
$restoreRes = make_request("$baseUrl/api.php?action=update_allocation", 'POST', $restorePayload);
echo "Restore Status: {$restoreRes['status']}\n";
echo "Restore Response: {$restoreRes['body']}\n\n";

unlink($cookieFile);
echo "Test finished.\n";
?>
