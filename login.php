<!DOCTYPE html>
<html>
<head>

<title>Login</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
rel="stylesheet"
href="assets/css/login.css">

</head>

<body>

<div class="login-card">

<h2>MJ SYSTEM</h2>

<p class="text-muted mb-4">
Inventory & Sales Management System
</p>

<form action="login_process.php"
method="POST">

<div class="mb-3">

<input
type="email"
name="email"
class="form-control"
placeholder="Email Address"
required>

</div>

<div class="mb-4">

<input
type="password"
name="password"
class="form-control"
placeholder="Password"
required>

</div>

<button
class="btn btn-primary btn-login">

Login

</button>

</form>

</div>

</body>
</html>