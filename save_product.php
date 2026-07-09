<?php
include 'config.php';

$barcode=$_POST['barcode'];
$product_name=$_POST['product_name'];
$buying_price=$_POST['buying_price'];
$selling_price=$_POST['selling_price'];
$quantity=$_POST['quantity'];

$sql="INSERT INTO products
(barcode,product_name,buying_price,selling_price,quantity)
VALUES
('$barcode','$product_name','$buying_price','$selling_price','$quantity')";

if(mysqli_query($conn,$sql)){
    header("Location: products.php");
}
else{
    echo mysqli_error($conn);
}
?>