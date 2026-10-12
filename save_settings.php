
<?php
require_once __DIR__ . '/includes/auth.php';
requireRole(['admin']);

require_once __DIR__ . '/config.php';

// Only accept form submissions
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Invalid request method.');
}

// Read submitted settings safely
$company_name = trim($_POST['company_name'] ?? '');
$address = trim($_POST['address'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$website = trim($_POST['website'] ?? '');
$currency = trim($_POST['currency'] ?? '');
$receipt_footer = trim($_POST['receipt_footer'] ?? '');

// Basic validation
if ($company_name === '' || $currency === '') {
    exit('Company name and currency are required.');
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit('Please enter a valid email address.');
}

// Update settings using a prepared statement
$stmt = $conn->prepare("
    UPDATE company_settings
    SET company_name = ?,
        address = ?,
        phone = ?,
        email = ?,
        website = ?,
        currency = ?,
        receipt_footer = ?
    WHERE id = 1
");

if (!$stmt) {
    error_log('Settings prepare failed: ' . $conn->error);
    http_response_code(500);
    exit('Unable to prepare the settings update.');
}

$stmt->bind_param(
    "sssssss",
    $company_name,
    $address,
    $phone,
    $email,
    $website,
    $currency,
    $receipt_footer
);

if ($stmt->execute()) {
    $stmt->close();
    header('Location: settings.php?success=1');
    exit();
}

error_log('Settings update failed: ' . $stmt->error);
$stmt->close();

http_response_code(500);
exit('Unable to save company settings. Please try again.');
?>
