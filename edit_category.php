
<?php
session_start();
require_once 'config.php';

mysqli_set_charset($conn, 'utf8mb4');

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['category_csrf'])) {
    $_SESSION['category_csrf'] = bin2hex(random_bytes(32));
}

$categoryId = filter_var($_GET['id'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT);

if (!$categoryId || $categoryId < 1) {
    header('Location: category.php');
    exit;
}

$stmt = $conn->prepare(
    'SELECT id, category_name FROM categories WHERE id = ?'
);
$stmt->bind_param('i', $categoryId);
$stmt->execute();
$stmt->bind_result($id, $categoryName);

if (!$stmt->fetch()) {
    $stmt->close();
    header('Location: category.php?notfound=1');
    exit;
}
$stmt->close();

$categoryName = (string)$categoryName;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $newName = trim($_POST['category_name'] ?? '');

    if (!is_string($token) ||
        !hash_equals($_SESSION['category_csrf'], $token)) {
        $error = 'Security check failed. Refresh the page and try again.';
    } elseif ($newName === '') {
        $error = 'Please enter a category name.';
    } elseif (mb_strlen($newName) > 100) {
        $error = 'Category name must not exceed 100 characters.';
    } else {
        $duplicate = $conn->prepare(
            'SELECT id FROM categories WHERE category_name = ? AND id <> ? LIMIT 1'
        );
        $duplicate->bind_param('si', $newName, $categoryId);
        $duplicate->execute();
        $duplicate->store_result();
        $exists = $duplicate->num_rows > 0;
        $duplicate->close();

        if ($exists) {
            $error = 'Another category already uses that name.';
        } else {
            $update = $conn->prepare(
                'UPDATE categories SET category_name = ? WHERE id = ?'
            );
            $update->bind_param('si', $newName, $categoryId);

            if ($update->execute()) {
                $update->close();
                header('Location: category.php?updated=1');
                exit;
            }

            $error = 'Could not update the category. Please try again.';
            $update->close();
        }
    }

    $categoryName = $newName;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category | InventoryPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/category.css">
</head>
<body>
<div class="container py-4 py-md-5" style="max-width: 760px;">

    <div class="mb-4">
        <a href="category.php" class="text-decoration-none">&larr; Back to Categories</a>
        <h1 class="h2 mt-3 mb-1">Edit Category</h1>
        <p class="text-muted">Update the category name below.</p>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="edit_category.php">
                <input type="hidden" name="id" value="<?= (int)$categoryId ?>">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['category_csrf']) ?>">

                <div class="mb-3">
                    <label for="category_name" class="form-label fw-semibold">
                        Category name
                    </label>
                    <input
                        type="text"
                        id="category_name"
                        name="category_name"
                        class="form-control"
                        maxlength="100"
                        value="<?= e($categoryName) ?>"
                        placeholder="Enter category name"
                        required
                        autofocus
                    >
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        Save Changes
                    </button>
                    <a href="category.php" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
</body>
</html>