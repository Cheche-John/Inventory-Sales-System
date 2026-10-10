```php
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

// Dashboard statistics
$total_products = 0;
$total_customers = 0;
$total_sales = 0;
$total_profit = 0;
$low_stock_count = 0;
$low_stock_products = [];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products");
if ($result) {
    $total_products = (int) mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM customers");
if ($result) {
    $total_customers = (int) mysqli_fetch_assoc($result)['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM sales");
if ($result) {
    $total_sales = (int) mysqli_fetch_assoc($result)['total'];
}

// Low stock means 5 units or fewer
$result = mysqli_query(
    $conn,
   "SELECT id, product_name, quantity
 FROM products
 WHERE quantity <= minimum_stock
 ORDER BY quantity ASC, product_name ASC"
);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $low_stock_products[] = $row;
    }

    $low_stock_count = count($low_stock_products);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard | MJ SYSTEM</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
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
        
        <?php if ($low_stock_count > 0) { ?>
            (<?= $low_stock_count; ?>)
        <?php } ?>
    </a>
    <a href="settings.php">⚙️ Settings</a>
    <a href="logout.php">🚪 Logout</a>

</div>

<div class="main">

    <h3>
        Welcome,
        <?= htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?>
    </h3>

    <p class="text-muted">
        Here's an overview of your inventory and sales.
    </p>

    <div class="row mt-4 g-3">

        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <h2><?= number_format($total_products); ?></h2>
                <p>Total Products</p>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <h2><?= number_format($total_customers); ?></h2>
                <p>Total Customers</p>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <h2><?= number_format($total_sales); ?></h2>
                <p>Total Sales</p>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <h2>KES <?= number_format($total_profit, 2); ?></h2>
                <p>Total Profit</p>
                <small>Profit calculation to be connected to sales records.</small>
            </div>
        </div>

    </div>

    <!-- Low Stock Alerts -->
    <div class="card shadow-sm mt-4" id="stock-alerts">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">🔔 Low Stock Alerts</h5>

            <?php if ($low_stock_count > 0) { ?>
                <span class="badge bg-danger">
                    <?= $low_stock_count; ?> product(s) need attention
                </span>
            <?php } else { ?>
                <span class="badge bg-success">Stock levels OK</span>
            <?php } ?>
        </div>

        <div class="card-body">

           <p class="text-muted">
    Products that have reached or fallen below their minimum stock levels are listed here.
</p>

            <?php if ($low_stock_count > 0) { ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">

                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Quantity Remaining</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($low_stock_products as $product) { ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($product['product_name']); ?>
                                    </td>

                                    <td>
                                        <?= (int) $product['quantity']; ?>
                                    </td>

                                    <td>
                                        <?php if ((int) $product['quantity'] === 0) { ?>
                                            <span class="badge bg-danger">Out of Stock</span>
                                        <?php } else { ?>
                                            <span class="badge bg-warning text-dark">
                                                Low Stock
                                            </span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>

                    </table>
                </div>

                <a href="products.php" class="btn btn-primary btn-sm">
                    View Products
                </a>

            <?php } else { ?>

                <div class="alert alert-success mb-0">
                    All products currently have more than 5 units in stock.
                </div>

            <?php } ?>

        </div>
    </div>

</div>

</body>
</html>
