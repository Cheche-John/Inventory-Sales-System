<?php
include 'config.php';

$barcode = $_POST['barcode'];
$product_name = $_POST['product_name'];
$category_id = $_POST['category_id'];
$buying_price = $_POST['buying_price'];
$best_price = $_POST['best_price'];
$selling_price = $_POST['selling_price'];
$quantity = $_POST['quantity'];
if(
    $buying_price < 0 ||
    $best_price < 0 ||
    $selling_price < 0 ||
    $quantity < 0
){
    die("Prices and quantity cannot be negative.");
}

$image = '';

if(isset($_FILES['image']) && $_FILES['image']['name']!=''){

    $image =
    time().'_'.$_FILES['image']['name'];

    move_uploaded_file(
        $_FILES['image']['tmp_name'],
        'assets/images/products/'.$image
    );
}

$sql =
INSERT INTO products
(
barcode,
product_name,
category_id,
buying_price,
best_price,
selling_price,
quantity,
image
)
(
'$barcode',
'$product_name',
'$category_id',
'$buying_price',
'$best_price',
'$selling_price',
'$quantity',
'$image'
)";

if(mysqli_query($conn,$sql)){
    header("Location: products.php");
}
else{
    echo mysqli_error($conn);
}
?>