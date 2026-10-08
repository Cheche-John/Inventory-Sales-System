<?php
include 'config.php';

$id = $_GET['id'];

$sale =
mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT * FROM sales
        WHERE id='$id'"
    )
);

$user =
mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT * FROM users
        WHERE id='{$sale['user_id']}'"
    )
);

$customer = null;

if($sale['customer_id']){

    $customer =
    mysqli_fetch_assoc(
        mysqli_query(
            $conn,
            "SELECT *
            FROM customers
            WHERE id='".$sale['customer_id']."'"
        )
    );

}

$company =
mysqli_fetch_assoc(
mysqli_query(
$conn,
"SELECT * FROM company_settings LIMIT 1"
)
);

$items =
mysqli_query(
    $conn,
    "SELECT
        sale_items.*,
        products.product_name
    FROM sale_items
    JOIN products
    ON sale_items.product_id = products.id
    WHERE sale_items.sale_id='$id'"
);
?>

<!DOCTYPE html>
<html>
<head>
<title>Receipt</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/receipt.css">
</head>
<body>

<div class="receipt">

<h2 class="text-center">
<?= $company['company_name']; ?>
</h2>

<p class="text-center text-muted">
Inventory & Sales Management System
</p>

<hr>

<p>
<strong>Receipt No:</strong>
<?= $sale['receipt_no']; ?>
</p>

<p>
<strong>Date:</strong>
<?= date('d M Y', strtotime($sale['created_at'])); ?>
</p>

<p>
<strong>Time:</strong>
<?= date('h:i:s A', strtotime($sale['created_at'])); ?>
</p>

<?php if($customer){ ?>

<p>
<strong>Customer:</strong>
<?= $customer['customer_name']; ?>
</p>

<?php if($customer['phone']){ ?>

<p>
<strong>Phone:</strong>
<?= $customer['phone']; ?>
</p>

<?php } ?>

<?php } else { ?>

<p>
<strong>Customer:</strong>
Cash Sale
</p>

<?php } ?>

<p>
<strong>Served By:</strong>
<?= $user['fullname']; ?>
</p>

<p>
<strong>Role:</strong>
<?= ucfirst($user['role']); ?>
</p>

<p>
<strong>Employee No:</strong>
<?= $user['employee_no']; ?>
</p>

<p>
<strong>Branch:</strong>
<?= $user['branch']; ?>
</p>

<p>
<strong>Payment Method:</strong>
<?= $sale['payment_method']; ?>
</p>

<hr>

<table class="table">

<tr>
<th>Product</th>
<th>Qty</th>
<th>Price</th>
<th>Total</th>
</tr>

<?php while($item=mysqli_fetch_assoc($items)){ ?>

<tr>

<td>
<?= $item['product_name']; ?>
</td>

<td>
<?= $item['quantity']; ?>
</td>

<td>
<?= number_format($item['price'],2); ?>
</td>

<td>
<?= number_format($item['total_price'],2); ?>
</td>

</tr>

<?php } ?>

</table>

<hr>

<h4>
Total:
<?= $company['currency']; ?>
<?= number_format($sale['total_amount'],2); ?>
</h4>

<hr>

<p class="text-center">
Thank you for shopping with us.
</p>

<p class="text-center">
📍 <?= $company['address']; ?>
</p>

<p class="text-center">
📞 <?= $company['phone']; ?>
</p>

<p class="text-center">
📧 <?= $company['email']; ?>
</p>

<p class="text-center">
<?= $company['receipt_footer']; ?>
</p>

<div class="text-center mt-3">

<img
src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode(
$company['company_name'].' | Receipt: '.$sale['receipt_no'].' | Date: '.date('d-m-Y', strtotime($sale['created_at']))
); ?>"
alt="QR Code">

<p class="mt-2">
Receipt Verification Code
</p>

</div>

<button
onclick="window.print()"
class="btn btn-success w-100">

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