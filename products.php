<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$result = mysqli_query($conn,"SELECT * FROM products");
?>

<!DOCTYPE html>
<html>
<head>
<title>Products</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="assets/css/style.css">

<link rel="stylesheet"
href="assets/css/products.css">

</head>
<body>

<div class="sidebar">

<h3 class="text-center p-4">
MJ SYSTEM
</h3>

<a href="dashboard.php">🏠 Dashboard</a>
<a href="products.php">📦 Products</a>
<a href="logout.php">🚪 Logout</a>

</div>

<div class="main">

<div class="d-flex justify-content-between mb-4">

<h2>Products</h2>

<a href="add_product.php"
class="btn btn-primary">
+ Add Product
</a>

</div>

<div class="card p-4">

<input
type="text"
id="search"
class="form-control mb-4"
placeholder="Search Product">

<div class="table-responsive">

<table class="table table-hover"
id="productTable">

<thead class="table-dark">

<tr>
<th>Image</th>
<th>Barcode</th>
<th>Name</th>
<th>Buying</th>
<th>Selling</th>
<th>Quantity</th>
<th>Profit</th>
<th>Action</th>
</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td>

<?php if($row['image']){ ?>

<img
src="assets/images/products/<?=
$row['image'];
?>"
width="50">

<?php } ?>

</td>

<td><?= $row['barcode']; ?></td>

<td><?= $row['product_name']; ?></td>

<td><?= $row['buying_price']; ?></td>

<td><?= $row['selling_price']; ?></td>

<td><?= $row['quantity']; ?></td>

<td>
<?= $row['selling_price']-$row['buying_price']; ?>
</td>

<td>

<a
href="edit_product.php?id=<?=
$row['id'];
?>"
class="btn btn-warning btn-sm">
Edit
</a>

<a
href="delete_product.php?id=<?=
$row['id'];
?>"
class="btn btn-danger btn-sm">
Delete
</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

<script>

document.getElementById("search")
.addEventListener("keyup",function(){

let filter =
this.value.toLowerCase();

let rows =
document.querySelectorAll(
"#productTable tbody tr"
);

rows.forEach(row=>{

let text =
row.textContent.toLowerCase();

row.style.display =
text.includes(filter)
?
""
:
"none";

});

});

</script>

</body>
</html>