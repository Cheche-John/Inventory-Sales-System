
<?php
session_start();
require_once 'config.php';

mysqli_set_charset($conn, 'utf8mb4');

if (empty($_SESSION['category_csrf'])) {
    $_SESSION['category_csrf'] = bin2hex(random_bytes(32));
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$search = trim($_GET['search'] ?? '');
$searchTerm = '%' . $search . '%';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $token = $_POST['csrf_token'] ?? '';
    $categoryId = filter_var(
        $_POST['category_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!is_string($token) ||
        !hash_equals($_SESSION['category_csrf'], $token)) {
        $error = 'Security check failed. Refresh the page and try again.';
    } elseif (!$categoryId || $categoryId < 1) {
        $error = 'Invalid category selected.';
    } else {
        $check = $conn->prepare(
            'SELECT COUNT(*) FROM products WHERE category_id = ?'
        );
        $check->bind_param('i', $categoryId);
        $check->execute();
        $check->bind_result($productCount);
        $check->fetch();
        $check->close();

        if ($productCount > 0) {
            $error = "This category cannot be deleted because it has {$productCount} product(s). Move those products to another category first.";
        } else {
            $delete = $conn->prepare(
                'DELETE FROM categories WHERE id = ?'
            );
            $delete->bind_param('i', $categoryId);

            if ($delete->execute() && $delete->affected_rows > 0) {
                $delete->close();
                header('Location: category.php?deleted=1');
                exit;
            } else {
                $error = 'Category was not deleted. It may already have been removed.';
            }

            $delete->close();
        }
    }
}

$stmt = $conn->prepare("
    SELECT c.id, c.category_name, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id
    WHERE c.category_name LIKE ?
    GROUP BY c.id, c.category_name
    ORDER BY c.category_name ASC
");
$stmt->bind_param('s', $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$totalCategories = 0;
$totalProducts = 0;

$countResult = $conn->query('SELECT COUNT(*) AS total FROM categories');
if ($countResult) {
    $totalCategories = (int)$countResult->fetch_assoc()['total'];
}

$productResult = $conn->query('SELECT COUNT(*) AS total FROM products');
if ($productResult) {
    $totalProducts = (int)$productResult->fetch_assoc()['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories | InventoryPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/category.css">
</head>
<body>

<div class="container py-4 py-md-5">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-muted small mb-1">Inventory management</p>
            <h1 class="h2 mb-1">Categories</h1>
            <p class="text-muted mb-0">Organise and manage your product categories.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="index.php" class="btn btn-outline-secondary">Home</a>
            <a href="dashboard.php" class="btn btn-outline-secondary">Dashboard</a>
            <a href="add_category.php" class="btn btn-primary">+ Add Category</a>
        </div>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Category deleted successfully.</div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small">Total categories</div>
                    <div class="display-6 fw-semibold"><?= $totalCategories ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small">Total products</div>
                    <div class="display-6 fw-semibold"><?= $totalProducts ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="category.php" class="row g-2 mb-4">
                <div class="col-sm">
                    <input
                        type="search"
                        name="search"
                        class="form-control"
                        placeholder="Search categories..."
                        value="<?= e($search) ?>"
                    >
                </div>
                <div class="col-sm-auto d-flex gap-2">
                    <button type="submit" class="btn btn-dark">Search</button>
                    <a href="category.php" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Category name</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th style="min-width: 190px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= (int)$row['id'] ?></td>
                                <td class="fw-semibold"><?= e($row['category_name']) ?></td>
                                <td><?= (int)$row['product_count'] ?></td>
                                <td>
                                    <?php if ((int)$row['product_count'] > 0): ?>
                                        <span class="badge text-bg-success">In use</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">No products</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a
                                            class="btn btn-sm btn-outline-primary"
                                            href="edit_category.php?id=<?= (int)$row['id'] ?>"
                                        >Edit</a>

                                        <form method="POST" action="category.php<?= $search !== '' ? '?search=' . urlencode($search) : '' ?>"
                                              onsubmit="return confirm('Are you sure you want to delete this category?');">
                                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['category_csrf']) ?>">
                                            <input type="hidden" name="category_id" value="<?= (int)$row['id'] ?>">
                                            <button type="submit" name="delete_category"
                                                    class="btn btn-sm btn-outline-danger">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No categories found.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <p class="text-muted small mt-3">
        Categories with products assigned to them cannot be deleted.
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$stmt->close();
?>