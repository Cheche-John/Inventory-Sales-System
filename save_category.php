```php
<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add_category.php");
    exit;
}

$category_name = trim($_POST['category_name'] ?? '');

if ($category_name === '') {
    header("Location: add_category.php?error=empty");
    exit;
}

$sql = "INSERT INTO categories (category_name) VALUES (?)";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "s", $category_name);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: category.php?added=1");
    exit;
} else {
    $error = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    die("Could not save category. Error: " . htmlspecialchars($error));
}
?>
```
