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

$cart = mysqli_query(
    $conn,
    "SELECT
        cart.*,
        products.product_name
    FROM cart
    JOIN products
    ON cart.product_id = products.id
    WHERE cart.user_id='".$_SESSION['user_id']."'"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales POS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="assets/css/sales.css">
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

        <div class="header-card text-center">

            <h2>🛒 Point Of Sale (POS)</h2>

            <p>
                Sell products and manage inventory efficiently.
            </p>

        </div>

        <div class="card-body p-4">

            <!-- Add Product Form -->
            <form action="add_to_cart.php" method="POST">

                <div class="mb-3">
                    <label class="fw-bold">
                        Product
                    </label>

                    <select
                        name="product_id"
                        class="form-select"
                        required>

                        <option value="">
                            Select Product
                        </option>

                        <?php while($product = mysqli_fetch_assoc($products)){ ?>

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

                <button
                    type="submit"
                    class="btn btn-success sale-btn">

                    🛒 Add To Cart

                </button>

            </form>

            <!-- Current Cart -->
            <h3 class="mt-5">
                🛒 Current Cart
            </h3>

            <table class="table table-bordered table-striped">

                <thead class="table-dark">
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                $total = 0;

                while($item = mysqli_fetch_assoc($cart)){

                    $subtotal =
                        $item['quantity'] *
                        $item['price'];

                    $total += $subtotal;
                ?>

                    <tr>

                        <td>
                            <?= $item['product_name']; ?>
                        </td>

                        <td>
                            <?= $item['quantity']; ?>
                        </td>

                        <td>
                            KSh <?= number_format($item['price'],2); ?>
                        </td>

                        <td>
                            KSh <?= number_format($subtotal,2); ?>
                        </td>

                    </tr>

                <?php } ?>

                <tr>

                    <th colspan="3">
                        Grand Total
                    </th>

                    <th>
                        KSh <?= number_format($total,2); ?>
                    </th>

                </tr>

                </tbody>

            </table>

            <!-- Checkout Form -->
            <?php if($total > 0){ ?>

            <form action="checkout.php" method="POST">

             <div class="mb-3">

    <label class="fw-bold">
        Customer (Optional)
    </label>

    <select
        name="customer_id"
        class="form-select">

        <option value="">
            Cash Sale
        </option>

        <?php

        $customers = mysqli_query(
            $conn,
            "SELECT *
            FROM customers
            ORDER BY customer_name"
        );

        while($customer =
            mysqli_fetch_assoc($customers))
        {
        ?>

            <option
                value="<?= $customer['id']; ?>">

                <?= $customer['customer_name']; ?>

                <?php
                if($customer['phone'] != ''){
                    echo ' - '.$customer['phone'];
                }
                ?>

            </option>

        <?php } ?>

    </select>

</div>
            
            <div class="mb-3">

                    <label class="fw-bold">
                        Payment Method
                    </label>

                    <select
                        name="payment_method"
                        class="form-select"
                        required>

                        <option value="">
                            Select Payment Method
                        </option>

                        <option value="Cash">
                            Cash
                        </option>

                        <option value="Mpesa">
                            Mpesa
                        </option>

                        <option value="Card">
                            Card
                        </option>

                        <option value="Bank Transfer">
                            Bank Transfer
                        </option>

                    </select>

                </div>

                <button
                    type="submit"
                    class="btn btn-success">

                    💰 Complete Sale

                </button>

            </form>

            <?php } ?>

        </div>

    </div>

</div>

</body>
</html>