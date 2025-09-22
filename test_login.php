<?php
require_once __DIR__ . '/Models/db.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Test kết nối database
echo "1. Database connection: OK\n";

// Test truy vấn user
$email = 'g@example.com';  // Thử với tài khoản mẫu
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    echo "2. User found: " . $user['full_name'] . "\n";
    echo "3. Stored password hash: " . $user['password'] . "\n";
    
    // Test password_verify
    $test_password = '123456';
    if (password_verify($test_password, $user['password'])) {
        echo "4. Password verify: OK - Match found!\n";
    } else {
        echo "4. Password verify: FAILED - No match\n";
    }
} else {
    echo "2. User not found\n";
}
?>