<?php
include 'config.php';

$result =
mysqli_query(
$conn,
"SELECT * FROM categories"
);
?>

<!DOCTYPE html>
<html>
<head>

<title>Categories</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<div class="d-flex justify-content-between mb-4">

<h2>Categories</h2>

<a href="add_category.php"
class="btn btn-primary">
+ Add Category
</a>

</div>

<table class="table table-bordered">

<thead class="table-dark">

<tr>
<th>ID</th>
<th>Name</th>
</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?= $row['id']; ?></td>
<td><?= $row['category_name']; ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</body>
</html>