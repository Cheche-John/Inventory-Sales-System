<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $website = mysqli_real_escape_string($conn, $_POST['website']);
    $currency = mysqli_real_escape_string($conn, $_POST['currency']);
    $receipt_footer = mysqli_real_escape_string($conn, $_POST['receipt_footer']);

    $sql = "
        UPDATE company_settings
        SET
            company_name='$company_name',
            address='$address',
            phone='$phone',
            email='$email',
            website='$website',
            currency='$currency',
            receipt_footer='$receipt_footer'
        WHERE id = 1
    ";

    if (mysqli_query($conn, $sql)) {
        header("Location: settings.php?success=1");
        exit();
    } else {
        die("Error: " . mysqli_error($conn));
    }
} else {
    die("Invalid request.");
}
?>