
<?php
require_once __DIR__ . '/includes/auth.php';
requireRole(['admin', 'manager', 'cashier']);

require_once 'config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: sales.php");
    exit();
}

$customer_id = null;

if (isset($_POST['customer_id']) && $_POST['customer_id'] !== '') {
    $customer_id = filter_var(
        $_POST['customer_id'],
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($customer_id === false) {
        die("Invalid customer selected.");
    }
}

$payment_method = $_POST['payment_method'] ?? 'Cash';

$allowed_methods = ['Cash', 'Mpesa', 'Card', 'Bank Transfer'];

if (!in_array($payment_method, $allowed_methods, true)) {
    die("Invalid payment method.");
}


try {
    $conn->begin_transaction();

    // Verify the selected customer, if one was provided.
    if ($customer_id !== null) {
        $stmt = $conn->prepare(
            "SELECT id FROM customers WHERE id = ?"
        );
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();

        if (!$stmt->get_result()->fetch_assoc()) {
            throw new Exception("The selected customer does not exist.");
        }

        $stmt->close();
    }

    // Load the current user's cart and product details.
    $stmt = $conn->prepare(
        "SELECT
            cart.product_id,
            cart.quantity,
            cart.price,
            products.buying_price,
            products.best_price,
            products.selling_price,
            products.product_name
         FROM cart
         INNER JOIN products
            ON cart.product_id = products.id
         WHERE cart.user_id = ?"
    );

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $items = [];

    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    $stmt->close();

    if (count($items) === 0) {
        throw new Exception("Your cart is empty.");
    }

    $total_amount = 0.0;

    // Validate quantities, selling prices, and available stock.
    foreach ($items as &$item) {
        $product_id = (int) $item['product_id'];
        $quantity = filter_var(
            $item['quantity'],
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($quantity === false) {
            throw new Exception("A cart item has an invalid quantity.");
        }

        // Lock the product row to prevent concurrent overselling.
        $stmt = $conn->prepare(
            "SELECT quantity, buying_price, best_price, selling_price
             FROM products
             WHERE id = ?
             FOR UPDATE"
        );

        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$product) {
            throw new Exception("A product in your cart no longer exists.");
        }

        if ($quantity > (int) $product['quantity']) {
            throw new Exception(
                "Insufficient stock for product ID " . $product_id .
                ". Available quantity: " . (int) $product['quantity']
            );
        }

        $price = (float) $item['price'];
        $best_price = (float) $product['best_price'];
        $selling_price = (float) $product['selling_price'];
        $buying_price = (float) $product['buying_price'];

        if (
            !is_finite($price) ||
            $price < $best_price ||
            $price > $selling_price
        ) {
            throw new Exception(
                "The selling price for product ID " . $product_id .
                " is outside the permitted price range."
            );
        }

        $item['quantity'] = $quantity;
        $item['price'] = $price;
        $item['buying_price'] = $buying_price;
        $item['subtotal'] = round($quantity * $price, 2);
        $item['profit'] = round(
            $quantity * ($price - $buying_price),
            2
        );

        $total_amount += $item['subtotal'];
    }
    unset($item);

    $total_amount = round($total_amount, 2);

    // Generate a receipt number.
    $receipt_no = 'REC-' . date('YmdHis') . '-' .
                  strtoupper(bin2hex(random_bytes(3)));

    // Create the sale. A NULL customer is allowed.
    $stmt = $conn->prepare(
        "INSERT INTO sales
            (customer_id, user_id, total_amount, payment_method, receipt_no)
         VALUES (?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "iidss",
        $customer_id,
        $user_id,
        $total_amount,
        $payment_method,
        $receipt_no
    );

    $stmt->execute();
    $sale_id = $conn->insert_id;
    $stmt->close();

    // Save each item and deduct stock.
    $item_stmt = $conn->prepare(
        "INSERT INTO sale_items
            (sale_id, product_id, quantity, price, total_price, profit)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    $stock_stmt = $conn->prepare(
        "UPDATE products
         SET quantity = quantity - ?
         WHERE id = ? AND quantity >= ?"
    );

    foreach ($items as $item) {
        $product_id = (int) $item['product_id'];
        $quantity = (int) $item['quantity'];
        $price = (float) $item['price'];
        $subtotal = (float) $item['subtotal'];
        $profit = (float) $item['profit'];

        $item_stmt->bind_param(
            "iiiddd",
            $sale_id,
            $product_id,
            $quantity,
            $price,
            $subtotal,
            $profit
        );

        $item_stmt->execute();

        $stock_stmt->bind_param(
            "iii",
            $quantity,
            $product_id,
            $quantity
        );

        $stock_stmt->execute();

        if ($stock_stmt->affected_rows !== 1) {
            throw new Exception(
                "Stock changed during checkout. Please try again."
            );
        }
    }

    $item_stmt->close();
    $stock_stmt->close();

    // Empty the cart only after the sale has been saved.
    $stmt = $conn->prepare(
        "DELETE FROM cart WHERE user_id = ?"
    );

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    header("Location: receipt.php?id=" . $sale_id);
    exit();

} catch (Throwable $e) {
    try {
        $conn->rollback();
    } catch (Throwable $rollback_error) {
        // The transaction may already have ended.
    }

    error_log("Checkout error: " . $e->getMessage());

    http_response_code(400);
    echo "<h3>Checkout could not be completed</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo '<p><a href="sales.php">Return to Sales</a></p>';
    exit();
}
?>
