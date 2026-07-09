<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "inventory_sales_system"
);

if(!$conn){
    die("Database Connection Failed");
}

?>