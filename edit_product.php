<?php
include 'config.php';

$id = $_GET['id'];

$result =
mysqli_query(
$conn,
"SELECT * FROM products WHERE id='$id'"
);

$product =
mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>

<title>Edit Product</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<div class="card p-4">

<h2>Edit Product</h2>

<form
action="update_product.php"
method="POST"
enctype="multipart/form-data">

<input
type="hidden"
name="id"
value="<?= $product['id']; ?>">

<div class="mb-3">
<label>Barcode</label>
<input
type="text"
name="barcode"
class="form-control"
value="<?= $product['barcode']; ?>">
</div>

<div class="mb-3">
<label>Product Name</label>
<input
type="text"
name="product_name"
class="form-control"
value="<?= $product['product_name']; ?>">
</div>

<div class="mb-3">
<label>Buying Price</label>
<input type="number" step="0.01" min="0" name="buying_price" ...>

<input type="number" step="0.01" min="0" name="selling_price" ...>

<input type="number" min="0" name="quantity" ...>

<div class="mb-3">
<label>New Image</label>
<input
type="file"
name="image"
class="form-control">
</div>

<button class="btn btn-primary">
Update Product
</button>

<a href="products.php"
class="btn btn-secondary">
Cancel
</a>

</form>

</div>

</div>

</body>
</html>