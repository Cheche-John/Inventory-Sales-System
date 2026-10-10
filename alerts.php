```php
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$message_type = 'success';

// Save a product's minimum stock level
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        die("Invalid request. Please refresh the page and try again.");
    }

    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $minimum_stock = filter_input(INPUT_POST, 'minimum_stock', FILTER_VALIDATE_INT);

    if (
        !$product_id ||
        $minimum_stock === false ||
        $minimum_stock === null ||
        $minimum_stock < 0
    ) {
        header("Location: alerts.php?error=invalid");
        exit();
    }

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE products SET minimum_stock = ? WHERE id = ?"
    );

    if (!$stmt) {
        die("Database error: " . htmlspecialchars(mysqli_error($conn)));
    }

    mysqli_stmt_bind_param($stmt, "ii", $minimum_stock, $product_id);

    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) >= 0) {
        mysqli_stmt_close($stmt);
        header("Location: alerts.php?updated=1");
        exit();
    }

    $error = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    die("Could not update stock level: " . htmlspecialchars($error));
}

if (isset($_GET['updated'])) {
    $message = "Minimum stock level updated successfully.";
}

if (isset($_GET['error']) && $_GET['error'] === 'invalid') {
    $message = "Please enter a valid minimum stock level.";
    $message_type = 'danger';
}

// Load all products so their thresholds can be managed
$result = mysqli_query(
    $conn,
    "SELECT id, barcode, product_name, quantity, minimum_stock
     FROM products
     ORDER BY
       CASE
         WHEN quantity = 0 THEN 0
         WHEN quantity <= minimum_stock THEN 1
         ELSE 2
       END,
       product_name ASC"
);

if (!$result) {
    die("Could not load products: " . htmlspecialchars(mysqli_error($conn)));
}

$products = [];
$low_stock_count = 0;
$out_of_stock_count = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;

    if ((int)$row['quantity'] === 0) {
        $out_of_stock_count++;
    }

    if (
        (int)$row['quantity'] > 0 &&
        (int)$row['quantity'] <= (int)$row['minimum_stock']
    ) {
        $low_stock_count++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Stock Alerts | MJ SYSTEM</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="sidebar">
    <h3 class="text-center p-4">MJ SYSTEM</h3>

    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="products.php">📦 Products</a>
    <a href="sales.php">🛒 Sales</a>
    <a href="customers.php">👥 Customers</a>
    <a href="category.php">📂 Categories</a>
    <a href="reports.php">📊 Reports</a>
    <a href="alerts.php">🔔 Alerts</a>
    <a href="settings.php">⚙️ Settings</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2>Stock Alerts</h2>
            <p class="text-muted mb-0">
                Set a minimum stock level for each product.
            </p>
        </div>

        <a href="products.php" class="btn btn-primary">
            View Products
        </a>
    </div>

    <?php if ($message !== '') { ?>
        <div class="alert alert-<?= $message_type; ?>">
            <?= htmlspecialchars($message); ?>
        </div>
    <?php } ?>

    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card shadow-sm p-3">
                <h3><?= count($products); ?></h3>
                <p class="mb-0">Total Products</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm p-3 border-warning">
                <h3><?= $low_stock_count; ?></h3>
                <p class="mb-0">Low Stock</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm p-3 border-danger">
                <h3><?= $out_of_stock_count; ?></h3>
                <p class="mb-0">Out of Stock</p>
            </div>
        </div>

    </div>

    <div class="card shadow-sm p-3">

        <div class="table-responsive">
            <table class="table table-hover align-middle">

                <thead class="table-dark">
                    <tr>
                        <th>Barcode</th>
                        <th>Product</th>
                        <th>Current Quantity</th>
                        <th>Minimum Stock</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (count($products) === 0) { ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            No products have been added yet.
                        </td>
                    </tr>
                <?php } ?>

                <?php foreach ($products as $product) {
                    $quantity = (int)$product['quantity'];
                    $minimum = (int)$product['minimum_stock'];

                    if ($quantity === 0) {
                        $status = 'Out of Stock';
                        $badge = 'bg-danger';
                    } elseif ($quantity <= $minimum) {
                        $status = 'Low Stock';
                        $badge = 'bg-warning text-dark';
                    } else {
                        $status = 'In Stock';
                        $badge = 'bg-success';
                    }
                ?>
                    <tr>
                        <td><?= htmlspecialchars($product['barcode'] ?? ''); ?></td>

                        <td><?= htmlspecialchars($product['product_name']); ?></td>

                        <td><?= $quantity; ?></td>

                        <td>
                            <form action="alerts.php" method="POST"
                                  class="d-flex gap-2 align-items-center">

                                <input type="hidden" name="csrf_token"
                                       value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

                                <input type="hidden" name="product_id"
                                       value="<?= (int)$product['id']; ?>">

                                <input
                                    type="number"
                                    name="minimum_stock"
                                    class="form-control form-control-sm"
                                    min="0"
                                    max="1000000"
                                    value="<?= $minimum; ?>"
                                    required
                                    style="width: 100px;"
                                    aria-label="Minimum stock for <?= htmlspecialchars($product['product_name']); ?>"
                                >

                                <button type="submit" class="btn btn-primary btn-sm">
                                    Save
                                </button>
                            </form>
                        </td>

                        <td>
                            <span class="badge <?= $badge; ?>">
                                <?= $status; ?>
                            </span>
                        </td>

                        <td>
                            <a href="edit_product.php?id=<?= (int)$product['id']; ?>"
                               class="btn btn-outline-secondary btn-sm">
                                Edit Product
                            </a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>

            </table>
        </div>

    </div>

</div>

</body>
</html>
