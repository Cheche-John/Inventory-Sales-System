
<?php
require_once __DIR__ . '/includes/auth.php';
requireRole(['admin']);

require_once __DIR__ . '/config.php';

// Safely display company settings
function settingsEscape($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// Fetch company settings
$result = mysqli_query($conn, "SELECT * FROM company_settings LIMIT 1");

if (!$result) {
    http_response_code(500);
    exit('Unable to load company settings.');
}

$company = mysqli_fetch_assoc($result);

if (!$company) {
    exit('No company settings found. Please check your company_settings table.');
}

$success = isset($_GET['success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Settings</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/settings.css">
</head>
<body>

<div class="container mt-5">

    <?php if ($success): ?>
        <div class="alert alert-success" role="alert">
            Settings saved successfully!
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3>⚙️ Company Settings</h3>
        </div>

        <div class="card-body">
            <form action="save_settings.php" method="POST">

                <div class="mb-3">
                    <label for="company_name" class="form-label">Company Name</label>
                    <input
                        type="text"
                        id="company_name"
                        name="company_name"
                        class="form-control"
                        value="<?= settingsEscape($company['company_name'] ?? '') ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <input
                        type="text"
                        id="address"
                        name="address"
                        class="form-control"
                        value="<?= settingsEscape($company['address'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        class="form-control"
                        value="<?= settingsEscape($company['phone'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        value="<?= settingsEscape($company['email'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="website" class="form-label">Website</label>
                    <input
                        type="text"
                        id="website"
                        name="website"
                        class="form-control"
                        value="<?= settingsEscape($company['website'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="currency" class="form-label">Currency</label>
                    <input
                        type="text"
                        id="currency"
                        name="currency"
                        class="form-control"
                        value="<?= settingsEscape($company['currency'] ?? '') ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label for="receipt_footer" class="form-label">Receipt Footer</label>
                    <textarea
                        id="receipt_footer"
                        name="receipt_footer"
                        class="form-control"
                        rows="3"><?= settingsEscape($company['receipt_footer'] ?? '') ?></textarea>
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
