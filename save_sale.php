<?php
session_start();
include 'config.php';

$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];
$price = $_POST['price'];

$user_id = $_SESSION['user_id'];

// Get product
$product_query = mysqli_query(
    $conn,
    "SELECT * FROM products WHERE id='$product_id'"
);

$product = mysqli_fetch_assoc($product_query);

if(!$product){
    die("Product not found.");
}

// Check stock
if($quantity > $product['quantity']){
    die("Insufficient stock.");
}

// Check selling price
if(
    $price < $product['best_price'] ||
    $price > $product['selling_price']
){
    die(
        "Price must be between KSh ".
        $product['best_price'].
        " and KSh ".
        $product['selling_price']
    );
}

// Calculations
$total_price = $quantity * $price;

$profit =
$quantity *
($price - $product['buying_price']);

// Create sale record
mysqli_query(
    $conn,
    "INSERT INTO sales
    (
        user_id,
        total_amount
    )
    VALUES
    (
        '$user_id',
        '$total_price'
    )"
);

$sale_id = mysqli_insert_id($conn);

// Save sale item
mysqli_query(
    $conn,
    "INSERT INTO sale_items
    (
        sale_id,
        product_id,
        quantity,
        price,
        total_price,
        profit
    )
    VALUES
    (
        '$sale_id',
        '$product_id',
        '$quantity',
        '$price',
        '$total_price',
        '$profit'
    )"
);

// Reduce stock
$new_stock =
$product['quantity'] -
$quantity;

mysqli_query(
    $conn,
    "UPDATE products
    SET quantity='$new_stock'
    WHERE id='$product_id'"
);

echo "
<script>
alert('Sale completed successfully!');
window.location='sales.php';
</script>
";
?>