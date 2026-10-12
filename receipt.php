
<?php
require_once __DIR__ . '/includes/auth.php';
requireRole(['admin', 'manager', 'cashier']);

require_once __DIR__ . '/config.php';

function receiptEscape($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit('Invalid receipt ID.');
}

// Fetch the sale
$stmt = $conn->prepare("SELECT * FROM sales WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$sale = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$sale) {
    http_response_code(404);
    exit('Receipt not found.');
}

// Fetch the user who processed the sale
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $sale['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch customer details, if available
$customer = null;

if (!empty($sale['customer_id'])) {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->bind_param("i", $sale['customer_id']);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Fetch company settings
$companyResult = $conn->query("SELECT * FROM company_settings LIMIT 1");
$company = $companyResult ? $companyResult->fetch_assoc() : null;

if (!$company) {
    $company = [
        'company_name' => 'Inventory & Sales',
        'currency' => 'KSh',
        'address' => '',
        'phone' => '',
        'email' => '',
        'receipt_footer' => 'Thank you for shopping with us.'
    ];
}

// Fetch items on this receipt
$stmt = $conn->prepare("
    SELECT sale_items.*, products.product_name
    FROM sale_items
    JOIN products ON sale_items.product_id = products.id
    WHERE sale_items.sale_id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$items = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?= receiptEscape($sale['receipt_no']) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/receipt.css">
</head>
<body>

<div class="receipt">

    <h2 class="text-center">
        <?= receiptEscape($company['company_name']) ?>
    </h2>

    <p class="text-center text-muted">
        Inventory &amp; Sales Management System
    </p>

    <hr>

    <p><strong>Receipt No:</strong>
        <?= receiptEscape($sale['receipt_no']) ?>
    </p>

    <p><strong>Date:</strong>
        <?= date('d M Y', strtotime($sale['created_at'])) ?>
    </p>

    <p><strong>Time:</strong>
        <?= date('h:i:s A', strtotime($sale['created_at'])) ?>
    </p>

    <?php if ($customer): ?>
        <p><strong>Customer:</strong>
            <?= receiptEscape($customer['customer_name'] ?? 'Customer') ?>
        </p>

        <?php if (!empty($customer['phone'])): ?>
            <p><strong>Phone:</strong>
                <?= receiptEscape($customer['phone']) ?>
            </p>
        <?php endif; ?>
    <?php else: ?>
        <p><strong>Customer:</strong> Cash Sale</p>
    <?php endif; ?>

    <p><strong>Served By:</strong>
        <?= receiptEscape($user['fullname'] ?? 'Unknown') ?>
    </p>

    <p><strong>Role:</strong>
        <?= receiptEscape(ucfirst($user['role'] ?? '')) ?>
    </p>

    <p><strong>Employee No:</strong>
        <?= receiptEscape($user['employee_no'] ?? '') ?>
    </p>

    <p><strong>Branch:</strong>
        <?= receiptEscape($user['branch'] ?? '') ?>
    </p>

    <p><strong>Payment Method:</strong>
        <?= receiptEscape($sale['payment_method']) ?>
    </p>

    <hr>

    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= receiptEscape($item['product_name']) ?></td>
                    <td><?= (int)$item['quantity'] ?></td>
                    <td><?= number_format((float)$item['price'], 2) ?></td>
                    <td><?= number_format((float)$item['total_price'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php
    $items->free();
    $stmt->close();
    ?>

    <hr>

    <h4>
        Total:
        <?= receiptEscape($company['currency']) ?>
        <?= number_format((float)$sale['total_amount'], 2) ?>
    </h4>

    <hr>

    <p class="text-center">Thank you for shopping with us.</p>

    <?php if (!empty($company['address'])): ?>
        <p class="text-center">📍 <?= receiptEscape($company['address']) ?></p>
    <?php endif; ?>

    <?php if (!empty($company['phone'])): ?>
        <p class="text-center">📞 <?= receiptEscape($company['phone']) ?></p>
    <?php endif; ?>

    <?php if (!empty($company['email'])): ?>
        <p class="text-center">📧 <?= receiptEscape($company['email']) ?></p>
    <?php endif; ?>

    <?php if (!empty($company['receipt_footer'])): ?>
        <p class="text-center">
            <?= receiptEscape($company['receipt_footer']) ?>
        </p>
    <?php endif; ?>

    <div class="text-center mt-3">
        <?php
        $qrData = $company['company_name']
            . ' | Receipt: ' . $sale['receipt_no']
            . ' | Date: ' . date('d-m-Y', strtotime($sale['created_at']));

        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data='
            . urlencode($qrData);
        ?>

        <img src="<?= receiptEscape($qrUrl) ?>" alt="Receipt QR Code">

        <p class="mt-2">Receipt Verification Code</p>
    </div>

    <button onclick="window.print()" class="btn btn-success w-100">
        🖨 Print Receipt
    </button>

    <a href="sales.php" class="btn btn-primary w-100 mt-2">
        🛒 New Sale
    </a>

    <a href="dashboard.php" class="btn btn-success w-100 mt-2">
        🏠 Dashboard
    </a>

    <a href="index.php" class="btn btn-secondary w-100 mt-2">
        🏡 Homepage
    </a>

</div>

</body>
</html>
