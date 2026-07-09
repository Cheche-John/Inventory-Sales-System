<!DOCTYPE html>
<html>
<head>

<title>Add Product</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<div class="card p-4">

<h3>Add Product</h3>

<form action="save_product.php"
method="POST">

<div class="mb-3">
<label>Barcode</label>
<input type="text"
name="barcode"
class="form-control">
</div>

<div class="mb-3">
<label>Product Name</label>
<input type="text"
name="product_name"
class="form-control"
required>
</div>

<div class="mb-3">
<label>Buying Price</label>
<input type="number"
step="0.01"
name="buying_price"
class="form-control"
required>
</div>

<div class="mb-3">
<label>Selling Price</label>
<input type="number"
step="0.01"
name="selling_price"
class="form-control"
required>
</div>

<div class="mb-3">
<label>Quantity</label>
<input type="number"
name="quantity"
class="form-control"
required>
</div>

<button class="btn btn-primary">
Save Product
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