<?php
require_once 'config.php';

echo "<h2>Debug: System Settings & Coupons</h2>";

// 1. Check Tables
function tableExists($pdo, $table) {
    try {
        $result = $pdo->query("SELECT 1 FROM $table LIMIT 1");
    } catch (Exception $e) {
        return false;
    }
    return $result !== false;
}

echo "<h3>Table Status:</h3>";
echo "system_settings: " . (tableExists($pdo, 'system_settings') ? 'EXISTS' : 'MISSING') . "<br>";
echo "coupons: " . (tableExists($pdo, 'coupons') ? 'EXISTS' : 'MISSING') . "<br>";

// 2. Test Settings
echo "<h3>Testing System Settings:</h3>";
$testKey = 'debug_test_key_' . time();
$testValue = 'debug_value';

// Update
$res = update_setting($pdo, $testKey, $testValue);
echo "Update Result: " . ($res['success'] ? 'Success' : 'Failed: ' . $res['message']) . "<br>";

// Get
$val = get_setting($pdo, $testKey);
echo "Get Result: " . ($val === $testValue ? 'Matches' : "Mismatch (Got: '$val')") . "<br>";

// 3. Test Coupons
echo "<h3>Testing Coupons:</h3>";
if (tableExists($pdo, 'coupons')) {
    $code = 'TEST' . time();
    $res = create_coupon($pdo, $code, 'fixed', 10.00, 50.00, date('Y-m-d', strtotime('+1 day')), 10);
    echo "Create Coupon '$code': " . ($res['success'] ? 'Success' : 'Failed: ' . $res['message']) . "<br>";
    
    $coupon = get_coupon_by_code($pdo, $code);
    echo "Get Coupon: " . ($coupon ? 'Found' : 'Not Found') . "<br>";
    
    // Clean up
    if ($coupon) {
        delete_coupon($pdo, $coupon['id']);
        echo "Cleanup: Deleted test coupon.<br>";
    }
} else {
    echo "Skipping Coupon test (Table missing)<br>";
}

?>
