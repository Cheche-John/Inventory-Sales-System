
<?php
include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm p-4 mx-auto" style="max-width: 600px;">

        <h2 class="mb-4">Add New Category</h2>

        <form action="save_category.php" method="POST">

            <div class="mb-3">
                <label for="category_name" class="form-label">
                    Category Name
                </label>

                <input
                    type="text"
                    id="category_name"
                    name="category_name"
                    class="form-control"
                    placeholder="Enter category name"
                    required
                    maxlength="100"
                >
            </div>

            <button type="submit" class="btn btn-primary">
                Save Category
            </button>

            <a href="category.php" class="btn btn-secondary">
                Cancel
            </a>

        </form>

    </div>
</div>

</body>
</html>
```
