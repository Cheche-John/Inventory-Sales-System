<?php
require_once __DIR__ . '/includes/auth.php';
requireRole(['admin', 'manager']);
include 'config.php';

$id = $_GET['id'];

mysqli_query(
$conn,
"DELETE FROM products WHERE id='$id'"
);

header("Location: products.php");
?>