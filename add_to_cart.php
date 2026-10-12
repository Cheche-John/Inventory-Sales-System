<?php

require_once __DIR__ . '/includes/auth.php';
requireRole(['admin', 'manager', 'cashier']);

include 'config.php';

$user_id = $_SESSION['user_id'];

$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];
$price = $_POST['price'];

$product =
mysqli_fetch_assoc(
mysqli_query(
$conn,
"SELECT * FROM products
WHERE id='$product_id'"
)
);

if(!$product){
    die("Product not found.");
}

if($quantity > $product['quantity']){
    die("Insufficient stock.");
}

if(
    $price < $product['best_price'] ||
    $price > $product['selling_price']
){
    die(
        "Price must be between ".
        $product['best_price'].
        " and ".
        $product['selling_price']
    );
}

mysqli_query(
$conn,
"INSERT INTO cart
(
user_id,
product_id,
quantity,
price
)
VALUES
(
'$user_id',
'$product_id',
'$quantity',
'$price'
)"
);

header("Location: sales.php");
exit();
?>