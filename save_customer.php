<?php
include 'config.php';

$customer_name =
mysqli_real_escape_string(
$conn,
$_POST['customer_name']
);

$phone =
mysqli_real_escape_string(
$conn,
$_POST['phone']
);

$email =
mysqli_real_escape_string(
$conn,
$_POST['email']
);

$address =
mysqli_real_escape_string(
$conn,
$_POST['address']
);

mysqli_query(
    $conn,
    "INSERT INTO customers
    (
        customer_name,
        phone,
        email,
        address
    )
    VALUES
    (
        '$customer_name',
        '$phone',
        '$email',
        '$address'
    )"
);

header(
"Location: customers.php"
);
exit();
?>