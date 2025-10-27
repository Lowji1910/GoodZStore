<?php
// Controllers/auth_controller.php
require_once __DIR__ . '/../Models/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function register_user($full_name, $email, $password, $phone) {
    global $conn;
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone_number, role) VALUES (?, ?, ?, ?, 'customer')");
    $stmt->bind_param("ssss", $full_name, $email, $hashed, $phone);
    if ($stmt->execute()) {
        return true;
    } else {
        return $stmt->error;
    }
}

function login_user($identifier, $password) {
    global $conn;
    // Cho phép đăng nhập bằng email hoặc số điện thoại
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR phone_number = ? LIMIT 1");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        $_SESSION['user_id'] = $user['id'];
        return $user['role'];
    }
    // Legacy plaintext support: if stored is not a bcrypt hash, allow one-time upgrade
    if ($user && substr($user['password'], 0, 4) !== '$2y$' && $password === $user['password']) {
        // Upgrade to bcrypt
        $newHash = password_hash($password, PASSWORD_BCRYPT);
        $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($upd) { $upd->bind_param('si', $newHash, $user['id']); $upd->execute(); }
        $_SESSION['user'] = [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        $_SESSION['user_id'] = $user['id'];
        return $user['role'];
    }
    return false;
}

function logout_user() {
    session_destroy();
}
