<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$products = mysqli_query(
    $conn,
    "SELECT * FROM products
    WHERE quantity > 0
    ORDER BY product_name"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales POS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/sales.css">
</head>
<body>

<div class="container mt-5">

    <div class="nav-buttons mb-4">

        <a href="dashboard.php" class="btn btn-primary">
            🏠 Dashboard
        </a>

        <a href="index.php" class="btn btn-secondary">
            🏡 Homepage
        </a>

        <a href="sales_report.php" class="btn btn-success">
            📊 Sales Report
        </a>

    </div>

    <div class="card pos-card">

        <div class="header-card">

            <h2>🛒 Point Of Sale (POS)</h2>

            <p>
                Sell products and manage inventory efficiently.
            </p>

        </div>

        <div class="card-body p-4">

            <form action="save_sale.php" method="POST">

                <div class="mb-3">
                    <label class="fw-bold">Product</label>

                    <select
                        name="product_id"
                        class="form-select"
                        required>

                        <option value="">
                            Select Product
                        </option>

                        <?php while($product=mysqli_fetch_assoc($products)){ ?>

                            <option value="<?= $product['id']; ?>">

                                <?= $product['product_name']; ?>

                                (Stock:
                                <?= $product['quantity']; ?>)

                            </option>

                        <?php } ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="fw-bold">
                        Quantity
                    </label>

                    <input
                        type="number"
                        name="quantity"
                        class="form-control"
                        min="1"
                        required>
                </div>

                <div class="mb-3">
                    <label class="fw-bold">
                        Selling Price
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        name="price"
                        class="form-control"
                        min="0"
                        required>

                    <small class="text-muted">
                        Price must be between Best Price and Selling Price.
                    </small>
                </div>

                <button class="btn btn-success sale-btn">

                    💰 Complete Sale

                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>