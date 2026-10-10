<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

$result = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");

if (!$result) {
    die("Could not load products: " . htmlspecialchars(mysqli_error($conn)));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Products | MJ SYSTEM</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/products.css">
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
    <a href="dashboard.php#stock-alerts">🔔 Alerts</a>
    <a href="settings.php">⚙️ Settings</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h2>Products</h2>

        <a href="add_product.php" class="btn btn-primary">
            + Add Product
        </a>
    </div>

    <?php if (isset($_GET['added'])) { ?>
        <div class="alert alert-success alert-dismissible fade show">
            Product saved successfully!
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <div class="card p-4 shadow-sm">

        <div class="row g-3 mb-4">

            <div class="col-md-8">
                <input
                    type="text"
                    id="search"
                    class="form-control"
                    placeholder="Search by barcode or product name..."
                >
            </div>

            <div class="col-md-4">
                <select id="stockFilter" class="form-select">
                    <option value="all">All Stock Levels</option>
                    <option value="out">Out of Stock</option>
                    <option value="low">Low Stock (1–5)</option>
                    <option value="in">In Stock (Above 5)</option>
                </select>
            </div>

        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle" id="productTable">

                <thead class="table-dark">
                    <tr>
                        <th>Image</th>
                        <th>Barcode</th>
                        <th>Name</th>
                        <th>Buying</th>
                        <th>Best Price</th>
                        <th>Selling</th>
                        <th>Quantity</th>
                        <th>Stock Status</th>
                        <th>Profit</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                    <?php
                    $quantity = (int) $row['quantity'];
                    $minimum_stock = (int) $row['minimum_stock'];

                    if ($quantity <= 0) {
    $stock_status = 'out';
    $status_label = 'Out of Stock';
    $badge_class = 'bg-danger';
} elseif ($quantity <= $minimum_stock) {
    $stock_status = 'low';
    $status_label = 'Low Stock';
    $badge_class = 'bg-warning text-dark';
} else {
    $stock_status = 'in';
    $status_label = 'In Stock';
    $badge_class = 'bg-success';
}

                    $buying_price = (float) $row['buying_price'];
                    $best_price = (float) $row['best_price'];
                    $selling_price = (float) $row['selling_price'];
                    $profit = $selling_price - $buying_price;
                    ?>

                    <tr data-stock="<?= $stock_status; ?>">

                        <td>
                            <?php if (!empty($row['image'])) { ?>
                                <img
                                    src="assets/images/products/<?= rawurlencode(basename($row['image'])); ?>"
                                    alt="<?= htmlspecialchars($row['product_name']); ?>"
                                    width="50"
                                    height="50"
                                    style="object-fit: cover; border-radius: 6px;"
                                >
                            <?php } else { ?>
                                <span class="text-muted">No image</span>
                            <?php } ?>
                        </td>

                        <td><?= htmlspecialchars($row['barcode'] ?? ''); ?></td>

                        <td><?= htmlspecialchars($row['product_name']); ?></td>

                        <td>KES <?= number_format($buying_price, 2); ?></td>

                        <td>KES <?= number_format($best_price, 2); ?></td>

                        <td>KES <?= number_format($selling_price, 2); ?></td>

                        <td><?= $quantity; ?></td>

                        <td>
                            <span class="badge <?= $badge_class; ?>">
                                <?= $status_label; ?>
                            </span>
                        </td>

                        <td>KES <?= number_format($profit, 2); ?></td>

                        <td>
                            <div class="d-flex gap-1">
                                <a
                                    href="edit_product.php?id=<?= (int) $row['id']; ?>"
                                    class="btn btn-warning btn-sm">
                                    Edit
                                </a>

                                <a
                                    href="delete_product.php?id=<?= (int) $row['id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure you want to delete this product?');">
                                    Delete
                                </a>
                            </div>
                        </td>

                    </tr>

                <?php } ?>

                </tbody>
            </table>

            <p id="noResults" class="text-muted text-center mt-3" style="display:none;">
                No products match your search or stock filter.
            </p>

        </div>
    </div>

</div>

<script>
const searchInput = document.getElementById('search');
const stockFilter = document.getElementById('stockFilter');
const rows = document.querySelectorAll('#productTable tbody tr');
const noResults = document.getElementById('noResults');

function filterProducts() {
    const searchText = searchInput.value.toLowerCase().trim();
    const selectedStock = stockFilter.value;
    let visibleCount = 0;

    rows.forEach(row => {
        const rowText = row.textContent.toLowerCase();
        const rowStock = row.dataset.stock;

        const matchesSearch = rowText.includes(searchText);
        const matchesStock =
            selectedStock === 'all' || rowStock === selectedStock;

        if (matchesSearch && matchesStock) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    noResults.style.display = visibleCount === 0 ? 'block' : 'none';
}

searchInput.addEventListener('input', filterProducts);
stockFilter.addEventListener('change', filterProducts);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
