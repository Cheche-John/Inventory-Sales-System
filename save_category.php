<?php
include 'config.php';

$category_name = $_POST['category_name'];

$sql = "INSERT INTO categories(category_name)
VALUES('$category_name')";
if(mysqli_query($conn,$sql)){
    header("Location: categories.php");
}
else{
    echo mysqli_error($conn);
}
?>