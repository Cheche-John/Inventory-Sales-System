<?php
include 'config.php';

$barcode = $_POST['barcode'];
$product_name = $_POST['product_name'];
$buying_price = $_POST['buying_price'];
$selling_price = $_POST['selling_price'];
$quantity = $_POST['quantity'];

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
"INSERT INTO products
(barcode,product_name,buying_price,selling_price,quantity,image)
VALUES
('$barcode',
'$product_name',
'$buying_price',
'$selling_price',
'$quantity',
'$image')";

if(mysqli_query($conn,$sql)){
    header("Location: products.php");
}
else{
    echo mysqli_error($conn);
}
?>