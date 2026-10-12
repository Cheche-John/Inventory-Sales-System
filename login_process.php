<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: login.php?error=missing');
    exit();
}

// Find the user securely
$stmt = $conn->prepare(
    "SELECT id, fullname, email, password, role
     FROM users
     WHERE email = ?
     LIMIT 1"
);

if (!$stmt) {
    die("Login could not be processed. Please try again.");
}

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

// Verify user and password
if (!$user || !password_verify($password, $user['password'])) {
    header('Location: login.php?error=invalid');
    exit();
}

// Accept only the roles used by this system
$allowed_roles = ['admin', 'manager', 'cashier'];

if (!in_array(strtolower($user['role']), $allowed_roles, true)) {
    header('Location: login.php?error=role');
    exit();
}

// Start a fresh session after successful login
session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'];
$_SESSION['fullname'] = $user['fullname'];
$_SESSION['role'] = strtolower($user['role']);

header('Location: dashboard.php');
exit();
?>