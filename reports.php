
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Date filters
$today = date('Y-m-d');
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? $today;

if (
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date) ||
    $start_date > $end_date
) {
    $start_date = date('Y-m-01');
    $end_date = $today;
}

// Financial summary
$sql = "
    SELECT
        COUNT(DISTINCT s.id) AS total_transactions,
        COALESCE(SUM(s.total_amount), 0) AS revenue,
        COALESCE(SUM(si.profit), 0) AS gross_profit,
        COALESCE(SUM(si.total_price - si.profit), 0) AS cost_of_goods
    FROM sales s
    LEFT JOIN sale_items si ON si.sale_id = s.id
    WHERE s.created_at >= ?
      AND s.created_at < DATE_ADD(?, INTERVAL 1 DAY)
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Sales history
$sql = "
    SELECT
        s.id,
        s.receipt_no,
        s.created_at,
        s.payment_method,
        s.total_amount,
        COALESCE(SUM(si.profit), 0) AS gross_profit
    FROM sales s
    LEFT JOIN sale_items si ON si.sale_id = s.id
    WHERE s.created_at >= ?
      AND s.created_at < DATE_ADD(?, INTERVAL 1 DAY)
    GROUP BY
        s.id, s.receipt_no, s.created_at,
        s.payment_method, s.total_amount
    ORDER BY s.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$sales_result = $stmt->get_result();

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Financial Reports | MJ SYSTEM</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .report-card {
            border: 0;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .06);
            height: 100%;
        }

        .report-value {
            font-size: 1.5rem;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .report-table th,
        .report-table td {
            vertical-align: middle;
        }
    </style>
</head>

<body>
<div class="container-fluid py-4 px-3 px-md-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Financial Reports</h2>
            <p class="text-muted mb-0">
                Review sales revenue, product costs, and gross profit.
            </p>
        </div>

        <a href="dashboard.php" class="btn btn-outline-secondary">
            Back to Dashboard
        </a>
    </div>

    <form method="GET" class="card report-card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">From date</label>
                    <input
                        type="date"
                        class="form-control"
                        id="start_date"
                        name="start_date"
                        value="<?= h($start_date) ?>"
                        required>
                </div>

                <div class="col-md-4">
                    <label for="end_date" class="form-label">To date</label>
                    <input
                        type="date"
                        class="form-control"
                        id="end_date"
                        name="end_date"
                        value="<?= h($end_date) ?>"
                        required>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Apply Filters
                    </button>
                    <a href="reports.php" class="btn btn-outline-secondary">
                        This Month
                    </a>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card report-card">
                <div class="card-body">
                    <p class="text-muted mb-2">Transactions</p>
                    <div class="report-value">
                        <?= number_format((int) $summary['total_transactions']) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card report-card">
                <div class="card-body">
                    <p class="text-muted mb-2">Sales Revenue</p>
                    <div class="report-value">
                        KES <?= number_format((float) $summary['revenue'], 2) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card report-card">
                <div class="card-body">
                    <p class="text-muted mb-2">Cost of Goods Sold</p>
                    <div class="report-value">
                        KES <?= number_format((float) $summary['cost_of_goods'], 2) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card report-card">
                <div class="card-body">
                    <p class="text-muted mb-2">Gross Profit</p>
                    <div class="report-value text-success">
                        KES <?= number_format((float) $summary['gross_profit'], 2) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card report-card">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Sales History</h5>

            <div class="table-responsive">
                <table class="table table-hover report-table">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Receipt No.</th>
                            <th>Payment</th>
                            <th>Revenue (KES)</th>
                            <th>Gross Profit (KES)</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($sales_result->num_rows > 0): ?>
                        <?php while ($sale = $sales_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= h($sale['created_at']) ?></td>
                                <td><?= h($sale['receipt_no']) ?></td>
                                <td><?= h($sale['payment_method']) ?></td>
                                <td><?= number_format((float) $sale['total_amount'], 2) ?></td>
                                <td><?= number_format((float) $sale['gross_profit'], 2) ?></td>
                                <td>
                                    <a
                                        class="btn btn-sm btn-outline-primary"
                                        href="receipt.php?id=<?= (int) $sale['id'] ?>">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No sales found for the selected dates.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <p class="text-muted small mt-3">
        Gross profit is revenue minus product buying costs. It does not
        include operating expenses, taxes, or other business costs.
    </p>

</div>
</body>
</html>
