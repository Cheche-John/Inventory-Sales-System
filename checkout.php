<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* Customer is optional */
$customer_id =
(
    isset($_POST['customer_id']) &&
    $_POST['customer_id'] != ''
)
?
$_POST['customer_id']
:
'NULL';

/* Payment method */
$payment_method =
isset($_POST['payment_method'])
?
$_POST['payment_method']
:
'Cash';

/* Get cart items */
$cart = mysqli_query(
    $conn,
    "SELECT
        cart.*,
        products.buying_price,
        products.quantity AS stock_quantity
    FROM cart
    JOIN products
    ON cart.product_id = products.id
    WHERE cart.user_id='$user_id'"
);

if(mysqli_num_rows($cart) == 0){
    die("Cart is empty.");
}

/* Calculate total */
$total_amount = 0;

while($row = mysqli_fetch_assoc($cart)){

    $subtotal =
        $row['quantity'] *
        $row['price'];

    $total_amount += $subtotal;
}

/* Generate receipt number */
$receipt_no =
"REC-".date("YmdHis");

/* Create sale */
mysqli_query(
    $conn,
    "INSERT INTO sales
    (
        customer_id,
        user_id,
        total_amount,
        payment_method,
        receipt_no
    )
    VALUES
    (
        $customer_id,
        '$user_id',
        '$total_amount',
        '$payment_method',
        '$receipt_no'
    )"
);

if(mysqli_error($conn)){
    die(mysqli_error($conn));
}

/* Get sale ID */
$sale_id = mysqli_insert_id($conn);

/* Get cart again */
$cart = mysqli_query(
    $conn,
    "SELECT
        cart.*,
        products.buying_price,
        products.quantity AS stock_quantity
    FROM cart
    JOIN products
    ON cart.product_id = products.id
    WHERE cart.user_id='$user_id'"
);

/* Save sale items */
while($item = mysqli_fetch_assoc($cart)){

    $subtotal =
        $item['quantity'] *
        $item['price'];

    $profit =
        $item['quantity'] *
        (
            $item['price']
            -
            $item['buying_price']
        );

    mysqli_query(
        $conn,
        "INSERT INTO sale_items
        (
            sale_id,
            product_id,
            quantity,
            price,
            total_price,
            profit
        )
        VALUES
        (
            '$sale_id',
            '{$item['product_id']}',
            '{$item['quantity']}',
            '{$item['price']}',
            '$subtotal',
            '$profit'
        )"
    );

    /* Reduce stock */
    $new_stock =
        $item['stock_quantity']
        -
        $item['quantity'];

    mysqli_query(
        $conn,
        "UPDATE products
        SET quantity='$new_stock'
        WHERE id='{$item['product_id']}'"
    );
}

/* Empty cart */
mysqli_query(
    $conn,
    "DELETE FROM cart
    WHERE user_id='$user_id'"
);

/* Go to receipt */
header(
    "Location: receipt.php?id=".$sale_id
);
exit();
?>