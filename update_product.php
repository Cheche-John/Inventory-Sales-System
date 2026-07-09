<?php
include 'config.php';

$id = $_POST['id'];
$barcode = $_POST['barcode'];
$product_name = $_POST['product_name'];
$buying_price = $_POST['buying_price'];
$selling_price = $_POST['selling_price'];
$quantity = $_POST['quantity'];

if($_FILES['image']['name']!=''){

    $image =
    time().'_'.$_FILES['image']['name'];

    move_uploaded_file(
        $_FILES['image']['tmp_name'],
        'assets/images/products/'.$image
    );

    $sql =
    "UPDATE products
    SET
    barcode='$barcode',
    product_name='$product_name',
    buying_price='$buying_price',
    selling_price='$selling_price',
    quantity='$quantity',
    image='$image'
    WHERE id='$id'";
}
else{

    $sql =
    "UPDATE products
    SET
    barcode='$barcode',
    product_name='$product_name',
    buying_price='$buying_price',
    selling_price='$selling_price',
    quantity='$quantity'
    WHERE id='$id'";
}

mysqli_query($conn,$sql);

header("Location: products.php");
?>