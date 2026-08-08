<?php
/**
 * CampusBite - Student Dashboard
 * Landing page for authenticated students
 */
require_once 'auth.php';
requireRole('student');

$user = getCurrentUser();
$pdo = getDB();

$totalCanteens = (int) $pdo->query('SELECT COUNT(*) FROM canteens')->fetchColumn();
$openCanteens = (int) $pdo->query("SELECT COUNT(*) FROM canteens WHERE status = 'open'")->fetchColumn();
$totalReviews = (int) $pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | CampusBite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="student_dashboard.php">
                <i class="bi bi-cup-hot-fill"></i> CampusBite
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="student_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-shop"></i> Browse Canteens</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="search.php"><i class="bi bi-search"></i> Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_complaints.php"><i class="bi bi-chat-left-text"></i> My Complaints</a>
                    </li>
                    <?php require 'includes/nav_auth.php'; ?>
                </ul>
            </div>
        </div>
    </nav>

    <section class="page-header">
        <div class="container">
            <h1 class="mb-2"><i class="bi bi-speedometer2"></i> Student Dashboard</h1>
            <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($user['full_name']) ?>!</p>
        </div>
    </section>

    <main class="container pb-5">
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card content-card text-center p-4">
                    <i class="bi bi-building display-4 text-success"></i>
                    <h3 class="mt-3 mb-0"><?= $totalCanteens ?></h3>
                    <p class="text-muted mb-0">Total Canteens</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card content-card text-center p-4">
                    <i class="bi bi-door-open display-4 text-success"></i>
                    <h3 class="mt-3 mb-0"><?= $openCanteens ?></h3>
                    <p class="text-muted mb-0">Open Now</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card content-card text-center p-4">
                    <i class="bi bi-star display-4 text-warning"></i>
                    <h3 class="mt-3 mb-0"><?= $totalReviews ?></h3>
                    <p class="text-muted mb-0">Student Reviews</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card content-card text-center p-4">
                    <i class="bi bi-person-circle display-4 text-primary"></i>
                    <h5 class="mt-3 mb-0"><?= htmlspecialchars($user['full_name']) ?></h5>
                    <p class="text-muted mb-0"><?= htmlspecialchars($user['university_id']) ?></p>
                    <a href="profile.php" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="bi bi-pencil"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <i class="bi bi-search"></i> Quick Search
            </div>
            <div class="card-body">
                <form method="GET" action="search.php">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control" name="q"
                                       placeholder="Search for food or canteens..." required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="type">
                                <option value="food">Food Items</option>
                                <option value="canteen">Canteens</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary-green w-100">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card content-card">
            <div class="card-header">
                <i class="bi bi-lightning"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3">
                    <a href="index.php" class="btn btn-primary-green">
                        <i class="bi bi-shop"></i> Browse All Canteens
                    </a>
                    <a href="search.php" class="btn btn-outline-success">
                        <i class="bi bi-search"></i> Search Food
                    </a>
                    <a href="profile.php" class="btn btn-outline-primary">
                        <i class="bi bi-person"></i> View Profile
                    </a>
                    <a href="change_password.php" class="btn btn-outline-secondary">
                        <i class="bi bi-key"></i> Change Password
                    </a>
                    <a href="my_complaints.php" class="btn btn-outline-info">
                        <i class="bi bi-chat-left-text"></i> My Complaints
                    </a>
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
