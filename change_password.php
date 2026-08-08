<?php
/**
 * CampusBite - Change Password
 * Allow users to change their password
 */
require_once 'auth.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDB();

$error = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $error = 'All fields are required.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif (!preg_match('/[A-Z]/', $newPassword)) {
        $error = 'New password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $newPassword)) {
        $error = 'New password must contain at least one number.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New passwords do not match.';
    } elseif ($currentPassword === $newPassword) {
        $error = 'New password must be different from current password.';
    } elseif (!verifyCurrentPassword($pdo, $user['user_id'], $currentPassword, $user['user_type'])) {
        $error = 'Current password is incorrect.';
    } else {
        if (changeUserPassword($pdo, $user['user_id'], $newPassword, $user['user_type'])) {
            logoutUser();
            header('Location: login.php?password_changed=1');
            exit;
        } else {
            $error = 'Failed to change password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | CampusBite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= $user['user_type'] === 'student' ? 'student_dashboard.php' : 'owner_dashboard.php' ?>">
                <i class="bi bi-cup-hot-fill"></i> CampusBite
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $user['user_type'] === 'student' ? 'student_dashboard.php' : 'owner_dashboard.php' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <?php if ($user['user_type'] === 'student'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php"><i class="bi bi-shop"></i> Browse Canteens</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="search.php"><i class="bi bi-search"></i> Search</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="my_complaints.php"><i class="bi bi-chat-left-text"></i> My Complaints</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php"><i class="bi bi-person"></i> Profile</a>
                    </li>
                    <?php require 'includes/nav_auth.php'; ?>
                </ul>
            </div>
        </div>
    </nav>

    <section class="page-header">
        <div class="container">
            <h1 class="mb-2"><i class="bi bi-key"></i> Change Password</h1>
            <p class="text-muted mb-0">Update your account password</p>
        </div>
    </section>

    <main class="container pb-5">
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show auto-dismiss" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card content-card">
                    <div class="card-header">
                        <i class="bi bi-shield-lock"></i> Change Password
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="change_password.php">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password"
                                           placeholder="Enter your current password" required>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password"
                                           placeholder="Enter new password" required minlength="6">
                                    <div class="form-text">Must be at least 6 characters with 1 uppercase letter and 1 number.</div>
                                </div>

                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                           placeholder="Re-enter new password" required minlength="6">
                                </div>

                                <button type="submit" class="btn btn-primary-green w-100">
                                    <i class="bi bi-key"></i> Change Password
                                </button>
                            </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer text-center">
        <div class="container">
            <p><strong>CampusBite</strong> &copy; 2026</p>
            <p>Designed for University Students</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
