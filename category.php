
<?php
session_start();
require_once 'config.php';

mysqli_set_charset($conn, "utf8mb4");

/* Search categories */
$search = trim($_GET['search'] ?? '');
$search_term = "%{$search}%";

$stmt = $conn->prepare("
    SELECT
        c.id,
        c.category_name,
        COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id
    WHERE c.category_name LIKE ?
    GROUP BY c.id, c.category_name
    ORDER BY c.category_name ASC
");

$stmt->bind_param("s", $search_term);
$stmt->execute();
$categories = $stmt->get_result();

/* Statistics */
$category_stats = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM categories"
);

$total_categories = (int)mysqli_fetch_assoc(
    $category_stats
)['total'];

$product_stats = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM products"
);

$total_products = (int)mysqli_fetch_assoc(
    $product_stats
)['total'];

$categories_found = $categories->num_rows;

function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Category Management | Inventory System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="assets/css/category.css">
</head>

<body>

<div class="app-container">

    <!-- TOP NAVIGATION -->
    <nav class="topbar">

        <a href="dashboard.php" class="brand">
            <span class="brand-logo">IS</span>

            <span class="brand-text">
                <strong>Inventory<span>Pro</span></strong>
                <small>Sales & Inventory Management</small>
            </span>
        </a>

        <div class="topbar-actions">

            <a href="index.php" class="nav-link-custom">
                Homepage
            </a>

            <a href="dashboard.php" class="dashboard-button">
                <span>⌂</span> Dashboard
            </a>

        </div>

    </nav>

    <main class="main-content">

        <!-- PAGE INTRODUCTION -->
        <section class="page-intro">

            <div>
                <div class="eyebrow">
                    INVENTORY ORGANIZATION
                </div>

                <h1>Category Management</h1>

                <p>
                    Organize your inventory, group related products
                    and keep your business running smoothly.
                </p>
            </div>

            <a href="add_category.php" class="add-category-button">
                <span>＋</span>
                Add New Category
            </a>

        </section>

        <!-- STATISTICS -->
        <section class="stats-grid">

            <div class="stat-card">
                <div class="stat-icon icon-purple">▦</div>

                <div>
                    <p>Total Categories</p>
                    <h2><?= $total_categories ?></h2>
                    <small>All registered categories</small>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-blue">▣</div>

                <div>
                    <p>Total Products</p>
                    <h2><?= $total_products ?></h2>
                    <small>Products in your inventory</small>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-green">⌕</div>

                <div>
                    <p>Categories Found</p>
                    <h2><?= $categories_found ?></h2>
                    <small>
                        <?= $search !== ''
                            ? 'Matching your search'
                            : 'Currently displayed' ?>
                    </small>
                </div>
            </div>

        </section>

        <!-- CATEGORY TABLE PANEL -->
        <section class="content-panel">

            <div class="panel-header">

                <div>
                    <h3>Your Categories</h3>

                    <p>
                        View your categories and the products
                        associated with each one.
                    </p>
                </div>

                <span class="total-badge">
                    <?= $categories_found ?>
                    <?= $categories_found === 1 ? 'category' : 'categories' ?>
                </span>

            </div>

            <!-- SEARCH -->
            <form method="GET" action="category.php" class="search-form">

                <span class="search-icon">⌕</span>

                <input
                    type="search"
                    name="search"
                    value="<?= e($search) ?>"
                    placeholder="Search by category name..."
                    aria-label="Search categories">

                <button type="submit">
                    Search
                </button>

                <?php if ($search !== ''): ?>
                    <a href="category.php" class="clear-button">
                        Clear
                    </a>
                <?php endif; ?>

            </form>

            <!-- TABLE -->
            <div class="table-responsive">

                <table class="table category-table">

                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Category ID</th>
                            <th>Products</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($categories_found > 0): ?>

                        <?php while ($row = mysqli_fetch_assoc($categories)): ?>

                            <tr>

                                <td>
                                    <div class="category-info">

                                        <span class="category-avatar">
                                            <?= e(strtoupper(
                                                substr($row['category_name'], 0, 1)
                                            )) ?>
                                        </span>

                                        <div>
                                            <strong>
                                                <?= e($row['category_name']) ?>
                                            </strong>

                                            <small>
                                                Product category
                                            </small>
                                        </div>

                                    </div>
                                </td>

                                <td>
                                    <span class="category-id">
                                        #<?= (int)$row['id'] ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="product-count">
                                        <?= (int)$row['product_count'] ?>
                                        <?= (int)$row['product_count'] === 1
                                            ? 'product'
                                            : 'products' ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if ((int)$row['product_count'] > 0): ?>

                                        <span class="status-badge active">
                                            ● In Use
                                        </span>

                                    <?php else: ?>

                                        <span class="status-badge empty">
                                            ○ No Products
                                        </span>

                                    <?php endif; ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="4" class="empty-state">

                                <div class="empty-icon">▦</div>

                                <h4>No Categories Found</h4>

                                <p>
                                    <?php if ($search !== ''): ?>
                                        Try another search term or clear your search.
                                    <?php else: ?>
                                        Start organizing your inventory by adding a category.
                                    <?php endif; ?>
                                </p>

                                <a
                                    href="add_category.php"
                                    class="add-category-button empty-add-button">
                                    ＋ Add Category
                                </a>

                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <div class="panel-footer">
                <span>Inventory Management System</span>
                <span>Category Management</span>
            </div>

        </section>

        <!-- HELPFUL TIP -->
        <section class="help-tip">
            <span class="tip-icon">💡</span>

            <div>
                <strong>Keep your inventory organized</strong>

                <p>
                    Create meaningful categories such as Beverages,
                    Electronics, Household Items or Gas Cylinders.
                    Products can then be grouped under the appropriate category.
                </p>
            </div>
        </section>

        <footer class="page-footer">
            &copy; <?= date('Y') ?> Inventory &amp; Sales Management System
        </footer>

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>