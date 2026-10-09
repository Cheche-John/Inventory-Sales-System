```php
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add_product.php");
    exit();
}

// Get and validate form data
$barcode = trim($_POST['barcode'] ?? '');
$product_name = trim($_POST['product_name'] ?? '');
$category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
$buying_price = $_POST['buying_price'] ?? '';
$best_price = $_POST['best_price'] ?? '';
$selling_price = $_POST['selling_price'] ?? '';
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if (
    $product_name === '' ||
    !$category_id ||
    !is_numeric($buying_price) ||
    !is_numeric($best_price) ||
    !is_numeric($selling_price) ||
    $quantity === false ||
    $quantity === null ||
    $quantity < 0 ||
    $buying_price < 0 ||
    $best_price < 0 ||
    $selling_price < 0
) {
    die("Invalid product details. Go back and check all fields.");
}

// Ensure the selected category exists
$check = mysqli_prepare(
    $conn,
    "SELECT id FROM categories WHERE id = ?"
);
mysqli_stmt_bind_param($check, "i", $category_id);
mysqli_stmt_execute($check);
$category_result = mysqli_stmt_get_result($check);

if (mysqli_num_rows($category_result) === 0) {
    mysqli_stmt_close($check);
    die("The selected category does not exist. Please choose a valid category.");
}
mysqli_stmt_close($check);

// Handle optional product image
$image_name = null;

if (
    isset($_FILES['image']) &&
    $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
) {
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        die("The image upload failed. Please try another image.");
    }

    $allowed_types = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    $file_tmp = $_FILES['image']['tmp_name'];

    if (!is_uploaded_file($file_tmp)) {
        die("Invalid image upload.");
    }

    $image_info = getimagesize($file_tmp);
    $mime_type = $image_info['mime'] ?? '';

    if (!isset($allowed_types[$mime_type])) {
        die("Invalid image. Please upload a JPG, PNG, or WEBP image.");
    }

    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        die("Image is too large. Maximum size is 5 MB.");
    }

    $upload_dir = __DIR__ . '/assets/images/products/';

    if (
        !is_dir($upload_dir) &&
        !mkdir($upload_dir, 0755, true)
    ) {
        die("Could not create the product image folder.");
    }

    $image_name = bin2hex(random_bytes(16)) . '.' .
                  $allowed_types[$mime_type];

    if (!move_uploaded_file(
        $file_tmp,
        $upload_dir . $image_name
    )) {
        die("Could not save the product image.");
    }
}

// Save product to database
$sql = "INSERT INTO products
        (barcode, product_name, category_id, buying_price,
         best_price, selling_price, quantity, image)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    if ($image_name !== null) {
        @unlink(__DIR__ . '/assets/images/products/' . $image_name);
    }

    die("Could not prepare product record: " .
        htmlspecialchars(mysqli_error($conn)));
}

mysqli_stmt_bind_param(
    $stmt,
    "ssidddis",
    $barcode,
    $product_name,
    $category_id,
    $buying_price,
    $best_price,
    $selling_price,
    $quantity,
    $image_name
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: products.php?added=1");
    exit();
}

$error = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);

if ($image_name !== null) {
    @unlink(__DIR__ . '/assets/images/products/' . $image_name);
}

die("Could not save product: " . htmlspecialchars($error));
?>
```
