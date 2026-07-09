<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Dashboard</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
rel="stylesheet"
href="assets/css/style.css">

<link
rel="stylesheet"
href="assets/css/dashboard.css">

</head>

<body>

<div class="sidebar">

<h3 class="text-center p-4">
MJ SYSTEM
</h3>

<a href="dashboard.php">
🏠 Dashboard
</a>

<a href="products.php">
📦 Products
</a>

<a href="#">
🛒 Sales
</a>

<a href="#">
 <a href="categories.php">
📂 Categories
</a>   
👥 Customers
</a>

<a href="#">
📊 Reports
</a>

<a href="#">
🔔 Alerts
</a>

<a href="logout.php">
🚪 Logout
</a>

</div>

<div class="main">

<h3>
Welcome,
<?php echo $_SESSION['fullname']; ?>
</h3>

<div class="row mt-4">

<div class="col-md-3">
<div class="stat-card">
<h2>0</h2>
<p>Total Products</p>
</div>
</div>

<div class="col-md-3">
<div class="stat-card">
<h2>0</h2>
<p>Total Customers</p>
</div>
</div>

<div class="col-md-3">
<div class="stat-card">
<h2>0</h2>
<p>Total Sales</p>
</div>
</div>

<div class="col-md-3">
<div class="stat-card">
<h2>KES 0</h2>
<p>Total Profit</p>
</div>
</div>

</div>

</div>

</body>
</html>