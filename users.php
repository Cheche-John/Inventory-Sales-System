<?php
require_once __DIR__ . '/includes/auth.php';
requireRole(['admin']);
require_once __DIR__ . '/config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $message = 'Invalid request. Refresh the page and try again.';
        $messageType = 'danger';
    } else {
        $action = $_POST['action'] ?? '';

        // Create a new user
        if ($action === 'create') {
            $fullname = trim($_POST['fullname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? '';
            $employeeNo = trim($_POST['employee_no'] ?? '');
            $branch = trim($_POST['branch'] ?? '');

            $allowedRoles = ['admin', 'manager', 'cashier'];

            if (
                $fullname === '' ||
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                strlen($password) < 8 ||
                !in_array($role, $allowedRoles, true)
            ) {
                $message = 'Enter a valid name, email, role and password of at least 8 characters.';
                $messageType = 'danger';
            } else {
                $check = $conn->prepare(
                    'SELECT id FROM users WHERE email = ? LIMIT 1'
                );
                $check->bind_param('s', $email);
                $check->execute();
                $check->store_result();

                if ($check->num_rows > 0) {
                    $message = 'That email address is already registered.';
                    $messageType = 'danger';
                    $check->close();
                } else {
                    $check->close();

                    $passwordHash = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $stmt = $conn->prepare(
                        'INSERT INTO users
                        (fullname, email, password, role, employee_no, branch)
                        VALUES (?, ?, ?, ?, ?, ?)'
                    );

                    $stmt->bind_param(
                        'ssssss',
                        $fullname,
                        $email,
                        $passwordHash,
                        $role,
                        $employeeNo,
                        $branch
                    );

                    if ($stmt->execute()) {
                        $message = 'User account created successfully.';
                    } else {
                        $message = 'Could not create the account. Check that the email and employee number are unique if required.';
                        $messageType = 'danger';
                    }

                    $stmt->close();
                }
            }
        }

        // Delete a user account
        elseif ($action === 'delete') {
            $deleteId = filter_input(
                INPUT_POST,
                'user_id',
                FILTER_VALIDATE_INT
            );

            $currentUserId = (int) $_SESSION['user_id'];

            if (!$deleteId || $deleteId === $currentUserId) {
                $message = 'You cannot delete your own logged-in account.';
                $messageType = 'danger';
            } else {
                $check = $conn->prepare(
                    'SELECT role FROM users WHERE id = ?'
                );
                $check->bind_param('i', $deleteId);
                $check->execute();
                $target = $check->get_result()->fetch_assoc();
                $check->close();

                if (!$target) {
                    $message = 'User account not found.';
                    $messageType = 'danger';
                } else {
                    // Do not remove the last administrator
                    if ($target['role'] === 'admin') {
                        $countResult = $conn->query(
                            "SELECT COUNT(*) AS total
                             FROM users
                             WHERE role = 'admin'"
                        );
                        $adminCount = (int) $countResult
                            ->fetch_assoc()['total'];
                    } else {
                        $adminCount = 2;
                    }

                    if (
                        $target['role'] === 'admin' &&
                        $adminCount <= 1
                    ) {
                        $message = 'You cannot delete the last administrator.';
                        $messageType = 'danger';
                    } else {
                        $stmt = $conn->prepare(
                            'DELETE FROM users WHERE id = ?'
                        );
                        $stmt->bind_param('i', $deleteId);

                        if ($stmt->execute() && $stmt->affected_rows > 0) {
                            $message = 'User account deleted successfully.';
                        } else {
                            $message = 'The account could not be deleted. It may be linked to existing sales records.';
                            $messageType = 'danger';
                        }

                        $stmt->close();
                    }
                }
            }
        }
    }
}

// Retrieve all users
$usersResult = $conn->query(
    'SELECT id, fullname, email, role, employee_no, branch, created_at
     FROM users
     ORDER BY id DESC'
);

if (!$usersResult) {
    die('Unable to load users. Please check your database connection.');
}

function e($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Management | MJ System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
            color: #243247;
        }

        .page-header {
            background: linear-gradient(135deg, #142b4a, #2463a6);
            color: white;
            padding: 28px;
            border-radius: 16px;
            margin-bottom: 24px;
        }

        .panel {
            background: white;
            border: 0;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(25, 45, 70, .07);
        }

        .table th {
            background: #f0f4f8;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .form-label {
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container-fluid px-3 px-md-4 py-4">

    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="h3 mb-2">User Management</h1>
            <p class="mb-0">Create and manage system user accounts.</p>
        </div>

        <a href="dashboard.php" class="btn btn-light">
            Back to Dashboard
        </a>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert alert-<?= e($messageType) ?> alert-dismissible fade show">
            <?= e($message) ?>
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="panel p-3 p-md-4 mb-4">
        <h2 class="h5 mb-3">Create a New Account</h2>

        <form method="POST" action="users.php">
            <input type="hidden" name="csrf_token"
                   value="<?= e($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="action" value="create">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="fullname" class="form-control"
                           maxlength="100" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control"
                           maxlength="100" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Temporary Password *</label>
                    <input type="password" name="password" class="form-control"
                           minlength="8" autocomplete="new-password" required>
                    <small class="text-muted">Use at least 8 characters.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Role *</label>
                    <select name="role" class="form-select" required>
                        <option value="">Choose a role</option>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="cashier">Cashier</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Employee Number</label>
                    <input type="text" name="employee_no" class="form-control"
                           maxlength="50" placeholder="e.g. MGR-002">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Branch</label>
                    <input type="text" name="branch" class="form-control"
                           maxlength="100" placeholder="e.g. Nairobi Main Branch">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        Create Account
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="panel p-3 p-md-4">
        <h2 class="h5 mb-3">Registered Users</h2>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Employee No.</th>
                        <th>Branch</th>
                        <th>Date Created</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($usersResult->num_rows === 0): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            No users found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while ($user = $usersResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int) $user['id'] ?></td>
                            <td><?= e($user['fullname']) ?></td>
                            <td><?= e($user['email']) ?></td>
                            <td>
                                <?php
                                $roleClass = [
                                    'admin' => 'danger',
                                    'manager' => 'primary',
                                    'cashier' => 'success'
                                ][$user['role']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= e($roleClass) ?>">
                                    <?= e(ucfirst($user['role'])) ?>
                                </span>
                            </td>
                            <td><?= e($user['employee_no']) ?></td>
                            <td><?= e($user['branch']) ?></td>
                            <td><?= e($user['created_at']) ?></td>
                            <td>
                                <?php if ((int) $user['id'] !== (int) $_SESSION['user_id']): ?>
                                    <form method="POST" action="users.php"
                                          onsubmit="return confirm('Are you sure you want to delete this account?');">
                                        <input type="hidden" name="csrf_token"
                                               value="<?= e($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id"
                                               value="<?= (int) $user['id'] ?>">
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger">
                                            Delete
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">Current account</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
