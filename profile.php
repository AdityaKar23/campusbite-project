<?php
/**
 * CampusBite - User Profile
 * View and edit profile information
 */
require_once 'auth.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDB();

$success = false;
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    
    $fullName = trim($_POST['full_name'] ?? '');
    
    if ($fullName === '') {
        $error = 'Full name cannot be empty.';
    } elseif (strlen($fullName) < 2) {
        $error = 'Full name must be at least 2 characters.';
    } elseif (strlen($fullName) > 100) {
        $error = 'Full name must not exceed 100 characters.';
    } else {
        if (updateUserProfile($pdo, $user['user_id'], $fullName, $user['user_type'])) {
            $success = true;
            // Update session with new name
            $_SESSION['full_name'] = $fullName;
            $user['full_name'] = $fullName;
        } else {
            $error = 'Failed to update profile. Please try again.';
        }
    }
}

// Get fresh user data
$userData = getUserById($pdo, $user['user_id'], $user['user_type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | CampusBite</title>
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
                        <a class="nav-link active" href="profile.php"><i class="bi bi-person"></i> Profile</a>
                    </li>
                    <?php require 'includes/nav_auth.php'; ?>
                </ul>
            </div>
        </div>
    </nav>

    <section class="page-header">
        <div class="container">
            <h1 class="mb-2"><i class="bi bi-person"></i> My Profile</h1>
            <p class="text-muted mb-0">View and manage your account information</p>
        </div>
    </section>

    <main class="container pb-5">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show auto-dismiss" role="alert">
                <i class="bi bi-check-circle"></i> Profile updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show auto-dismiss" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card content-card text-center">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="bi bi-person-circle display-1 text-muted"></i>
                        </div>
                        <h4 class="mb-2"><?= htmlspecialchars($user['full_name']) ?></h4>
                        <p class="text-muted mb-3"><?= $user['user_type'] === 'student' ? 'Student' : 'Canteen Owner' ?></p>
                        <div class="badge bg-success mb-3">Active</div>
                        <hr>
                        <div class="text-start">
                            <p class="mb-2"><strong><i class="bi bi-id-card"></i> University ID:</strong></p>
                            <p class="text-muted mb-3"><?= htmlspecialchars($user['university_id']) ?></p>
                            <?php if ($user['user_type'] === 'student' && !empty($userData['department'])): ?>
                                <p class="mb-2"><strong><i class="bi bi-building"></i> Department:</strong></p>
                                <p class="text-muted mb-3"><?= htmlspecialchars($userData['department']) ?></p>
                            <?php endif; ?>
                            <p class="mb-2"><strong><i class="bi bi-calendar"></i> Member Since:</strong></p>
                            <p class="text-muted">2026</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card content-card">
                    <div class="card-header">
                        <i class="bi bi-pencil-square"></i> Edit Profile
                    </div>
                    <div class="card-body">
                        <form method="POST" action="profile.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name"
                                       value="<?= htmlspecialchars($user['full_name']) ?>" required
                                       minlength="2" maxlength="100">
                                <div class="form-text">Enter your full name as it appears in official records.</div>
                            </div>

                            <div class="mb-3">
                                <label for="university_id" class="form-label">University ID</label>
                                <input type="text" class="form-control" id="university_id"
                                       value="<?= htmlspecialchars($user['university_id']) ?>" disabled>
                                <div class="form-text">University ID cannot be changed.</div>
                            </div>

                            <div class="mb-3">
                                <label for="role" class="form-label">Account Type</label>
                                <input type="text" class="form-control" id="role"
                                       value="<?= $user['user_type'] === 'student' ? 'Student' : 'Canteen Owner' ?>" disabled>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary-green">
                                    <i class="bi bi-check-circle"></i> Update Profile
                                </button>
                                <a href="change_password.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-key"></i> Change Password
                                </a>
                                <?php if ($user['user_type'] === 'student'): ?>
                                    <a href="my_complaints.php" class="btn btn-outline-info">
                                        <i class="bi bi-chat-left-text"></i> My Complaints
                                    </a>
                                <?php endif; ?>
                            </div>
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
