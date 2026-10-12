<?php
require_once __DIR__ . '/includes/auth.php';
requireRole(['admin', 'manager', 'cashier']);

include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

$products = mysqli_query(
    $conn,
    "SELECT * FROM products
     WHERE quantity > 0
     ORDER BY product_name"
);

$cart_stmt = $conn->prepare(
    "SELECT cart.*, products.product_name
     FROM cart
     JOIN products ON cart.product_id = products.id
     WHERE cart.user_id = ?"
);
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart = $cart_stmt->get_result();

$customers = mysqli_query(
    $conn,
    "SELECT * FROM customers ORDER BY customer_name"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales POS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="assets/css/sales.css">
</head>
<body>

<div class="container mt-5 mb-5">

    <div class="nav-buttons mb-4">
        <a href="dashboard.php" class="btn btn-primary">🏠 Dashboard</a>
        <a href="index.php" class="btn btn-secondary">🏡 Homepage</a>
        <a href="sales_report.php" class="btn btn-success">📊 Sales Report</a>
    </div>

    <div class="card pos-card">
        <div class="header-card text-center">
            <h2>🛒 Point Of Sale (POS)</h2>
            <p>Sell products and manage inventory efficiently.</p>
        </div>

        <div class="card-body p-4">

            <form action="add_to_cart.php" method="POST" id="saleForm">

                <div class="mb-3">
                    <label for="barcode" class="fw-bold">
                        📦 Scan or Enter Product Barcode
                    </label>

                    <input
                        type="text"
                        id="barcode"
                        class="form-control"
                        placeholder="Scan barcode here..."
                        autocomplete="off"
                        autofocus>

                    <small class="text-muted">
                        Scan the barcode, or enter it and press Enter.
                    </small>

                    <div id="barcodeMessage" class="mt-2" role="status"></div>
                </div>

                <div class="mb-3">
                    <label for="product_id" class="fw-bold">Product</label>

                    <select
                        name="product_id"
                        id="product_id"
                        class="form-select"
                        required>

                        <option value="">Select Product</option>

                        <?php while ($product = mysqli_fetch_assoc($products)) { ?>
                            <option
                                value="<?= (int) $product['id']; ?>"
                                data-barcode="<?= htmlspecialchars($product['barcode'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                data-stock="<?= (int) $product['quantity']; ?>"
                                data-best-price="<?= htmlspecialchars($product['best_price'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-selling-price="<?= htmlspecialchars($product['selling_price'], ENT_QUOTES, 'UTF-8'); ?>">

                                <?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8'); ?>
                                (Stock: <?= (int) $product['quantity']; ?>)
                            </option>
                        <?php } ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label for="quantity" class="fw-bold">Quantity</label>
                    <input
                        type="number"
                        name="quantity"
                        id="quantity"
                        class="form-control"
                        min="1"
                        value="1"
                        required>
                    <small id="stockMessage" class="text-muted"></small>
                </div>

                <div class="mb-3">
                    <label for="price" class="fw-bold">Selling Price (KSh)</label>
                    <input
                        type="number"
                        step="0.01"
                        name="price"
                        id="price"
                        class="form-control"
                        min="0"
                        required>

                    <small class="text-muted">
                        Use a price between the product's Best Price and Selling Price.
                    </small>
                </div>

                <button type="submit" class="btn btn-success sale-btn">
                    🛒 Add To Cart
                </button>
            </form>

            <h3 class="mt-5">🛒 Current Cart</h3>

            <div class="table-responsive">
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

                    while ($item = mysqli_fetch_assoc($cart)) {
                        $subtotal = (float) $item['quantity'] * (float) $item['price'];
                        $total += $subtotal;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= (int) $item['quantity']; ?></td>
                            <td>KSh <?= number_format((float) $item['price'], 2); ?></td>
                            <td>KSh <?= number_format($subtotal, 2); ?></td>
                        </tr>
                    <?php } ?>

                        <tr>
                            <th colspan="3">Grand Total</th>
                            <th>KSh <?= number_format($total, 2); ?></th>
                        </tr>
                    </tbody>
                </table>
            </div>

            <?php if ($total > 0) { ?>
                <form action="checkout.php" method="POST">

                    <div class="mb-3">
                        <label class="fw-bold">Customer (Optional)</label>

                        <select name="customer_id" class="form-select">
                            <option value="">Cash Sale</option>

                            <?php while ($customer = mysqli_fetch_assoc($customers)) { ?>
                                <option value="<?= (int) $customer['id']; ?>">
                                    <?= htmlspecialchars($customer['customer_name'], ENT_QUOTES, 'UTF-8'); ?>
                                    <?php
                                    if (!empty($customer['phone'])) {
                                        echo ' - ' . htmlspecialchars($customer['phone'], ENT_QUOTES, 'UTF-8');
                                    }
                                    ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Payment Method</label>

                        <select name="payment_method" class="form-select" required>
                            <option value="">Select Payment Method</option>
                            <option value="Cash">Cash</option>
                            <option value="Mpesa">Mpesa</option>
                            <option value="Card">Card</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">
                        💰 Complete Sale
                    </button>
                </form>
            <?php } ?>

        </div>
    </div>
</div>

<script>
const barcodeInput = document.getElementById('barcode');
const productSelect = document.getElementById('product_id');
const quantityInput = document.getElementById('quantity');
const priceInput = document.getElementById('price');
const barcodeMessage = document.getElementById('barcodeMessage');
const stockMessage = document.getElementById('stockMessage');

function selectProductByBarcode() {
    const barcode = barcodeInput.value.trim();

    if (!barcode) {
        barcodeMessage.textContent = 'Please scan or enter a barcode.';
        barcodeMessage.className = 'mt-2 text-warning';
        return;
    }

    let match = null;

    for (const option of productSelect.options) {
        if (
            option.value !== '' &&
            option.dataset.barcode &&
            option.dataset.barcode.trim() === barcode
        ) {
            match = option;
            break;
        }
    }

    if (!match) {
        barcodeMessage.textContent =
            'Product not found. Check that this product has the correct barcode saved.';
        barcodeMessage.className = 'mt-2 text-danger';
        productSelect.value = '';
        return;
    }

    productSelect.value = match.value;
    updateSelectedProduct();

    barcodeMessage.textContent = 'Product found: ' + match.text.trim();
    barcodeMessage.className = 'mt-2 text-success';

    quantityInput.focus();
    quantityInput.select();
}

function updateSelectedProduct() {
    const option = productSelect.options[productSelect.selectedIndex];

    if (!option || !option.value) {
        stockMessage.textContent = '';
        return;
    }

    const stock = Number(option.dataset.stock || 0);
    const bestPrice = option.dataset.bestPrice;
    const sellingPrice = option.dataset.sellingPrice;

    stockMessage.textContent = 'Available stock: ' + stock;

    priceInput.min = bestPrice;
    priceInput.max = sellingPrice;

    // Suggest the normal selling price when selecting a product.
    priceInput.value = sellingPrice;
}

barcodeInput.addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        selectProductByBarcode();
    }
});

productSelect.addEventListener('change', function() {
    updateSelectedProduct();
    barcodeMessage.textContent = '';
});

document.getElementById('saleForm').addEventListener('submit', function(event) {
    const option = productSelect.options[productSelect.selectedIndex];

    if (!option || !option.value) {
        event.preventDefault();
        alert('Please scan a barcode or select a product.');
        barcodeInput.focus();
        return;
    }

    const quantity = Number(quantityInput.value);
    const stock = Number(option.dataset.stock || 0);
    const price = Number(priceInput.value);
    const bestPrice = Number(option.dataset.bestPrice);
    const sellingPrice = Number(option.dataset.sellingPrice);

    if (quantity < 1 || quantity > stock) {
        event.preventDefault();
        alert('Enter a quantity between 1 and the available stock (' + stock + ').');
        quantityInput.focus();
        return;
    }

    if (price < bestPrice || price > sellingPrice) {
        event.preventDefault();
        alert('The price must be between KSh ' + bestPrice + ' and KSh ' + sellingPrice + '.');
        priceInput.focus();
    }
});
</script>

</body>
</html>