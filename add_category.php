<!DOCTYPE html>
<html>
<head>
<title>Add Category</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">
</head>

<body>

<div class="container mt-5">

<div class="card p-4">

<h2>Add Category</h2>

<form action="save_category.php"
method="POST">

<div class="mb-3">

<label>Category Name</label>

<input type="text"
name="category_name"
class="form-control">
required>

</div>

<button class="btn btn-primary">
Save Category
</button>

<a href="categories.php"
class="btn btn-secondary">
Cancel
</a>

</form>

</div>

</div>

</body>
</html>