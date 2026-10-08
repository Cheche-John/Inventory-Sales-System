<?php
session_start();
include 'config.php';

if($_SESSION['role'] != 'admin'){
    die("Access denied.");
}

$company = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT * FROM company_settings LIMIT 1"
    )
);

if (isset($_GET['success'])) {
    echo "<div class='alert alert-success'>
            Settings saved successfully!
          </div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Company Settings</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/settings.css">
</head>
<body>

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-primary text-white">
    <h3>⚙️ Company Settings</h3>
</div>

<div class="card-body">

<form action="save_settings.php" method="POST">

<div class="mb-3">
<label>Company Name</label>
<input
type="text"
name="company_name"
class="form-control"
value="<?= $company['company_name']; ?>">
</div>

<div class="mb-3">
<label>Address</label>
<input
type="text"
name="address"
class="form-control"
value="<?= $company['address']; ?>">
</div>

<div class="mb-3">
<label>Phone</label>
<input
type="text"
name="phone"
class="form-control"
value="<?= $company['phone']; ?>">
</div>

<div class="mb-3">
<label>Email</label>
<input
type="email"
name="email"
class="form-control"
value="<?= $company['email']; ?>">
</div>

<div class="mb-3">
<label>Website</label>
<input
type="text"
name="website"
class="form-control"
value="<?= $company['website']; ?>">
</div>

<div class="mb-3">
<label>Currency</label>
<input
type="text"
name="currency"
class="form-control"
value="<?= $company['currency']; ?>">
</div>

<div class="mb-3">
<label>Receipt Footer</label>
<textarea
name="receipt_footer"
class="form-control"
rows="3"><?= $company['receipt_footer']; ?></textarea>
</div>

<button type="submit" class="btn btn-success">
    💾 Save Settings
</button>

<a href="dashboard.php" class="btn btn-secondary">
🏠 Dashboard
</a>

</form>

</div>

</div>

</div>

</body>
</html>