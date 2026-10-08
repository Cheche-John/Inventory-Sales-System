<?php
session_start();
include 'config.php';

$customers = mysqli_query(
    $conn,
    "SELECT *
    FROM customers
    ORDER BY customer_name ASC"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Management</title>

    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">

    <link
    rel="stylesheet"
    href="assets/css/customers.css">
</head>
<body>

<div class="container mt-5">

    <div class="d-flex justify-content-between mb-4">

        <h2>👥 Customer Management</h2>

        <div>

            <a
            href="dashboard.php"
            class="btn btn-primary">
            🏠 Dashboard
            </a>

            <a
            href="sales.php"
            class="btn btn-success">
            🛒 Sales
            </a>

        </div>

    </div>

    <div class="card shadow mb-4">

        <div class="card-header">
            ➕ Add New Customer
        </div>

        <div class="card-body">

            <form
            action="save_customer.php"
            method="POST">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>
                            Customer Name
                        </label>

                        <input
                        type="text"
                        name="customer_name"
                        class="form-control"
                        required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>
                            Phone
                        </label>

                        <input
                        type="text"
                        name="phone"
                        class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>
                            Email
                        </label>

                        <input
                        type="email"
                        name="email"
                        class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>
                            Address
                        </label>

                        <input
                        type="text"
                        name="address"
                        class="form-control">
                    </div>

                </div>

                <button
                type="submit"
                class="btn btn-success">

                💾 Save Customer

                </button>

            </form>

        </div>

    </div>

    <div class="card shadow">

        <div class="card-header">
            📋 Customer List
        </div>

        <div class="card-body">

            <table
            class="table table-bordered table-hover">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address</th>
                    </tr>

                </thead>

                <tbody>

                    <?php
                    while(
                        $customer =
                        mysqli_fetch_assoc($customers)
                    ){
                    ?>

                    <tr>

                        <td>
                            <?= $customer['id']; ?>
                        </td>

                        <td>
                            <?= $customer['customer_name']; ?>
                        </td>

                        <td>
                            <?= $customer['phone']; ?>
                        </td>

                        <td>
                            <?= $customer['email']; ?>
                        </td>

                        <td>
                            <?= $customer['address']; ?>
                        </td>

                    </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>
</html>