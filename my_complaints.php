<?php
/**
 * CampusBite - My Complaints
 * Students can view all their submitted complaints
 */
require_once 'auth.php';
requireRole('student');

$user = getCurrentUser();
$pdo = getDB();

// Get all user's complaints
$complaints = getComplaintsByUser($pdo, $user['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints | CampusBite</title>
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
                        <a class="nav-link" href="student_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-shop"></i> Browse Canteens</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="search.php"><i class="bi bi-search"></i> Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="my_complaints.php"><i class="bi bi-chat-left-text"></i> My Complaints</a>
                    </li>
                    <?php require 'includes/nav_auth.php'; ?>
                </ul>
            </div>
        </div>
    </nav>

    <section class="page-header">
        <div class="container">
            <h1 class="mb-2"><i class="bi bi-chat-left-text"></i> My Complaints</h1>
            <p class="text-muted mb-0">View all your submitted complaints</p>
        </div>
    </section>

    <main class="container pb-5">
        <?php if (empty($complaints)): ?>
            <div class="card content-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-chat-left-dots display-1 text-muted"></i>
                    <p class="mt-3 text-muted">No complaints submitted yet.</p>
                    <a href="index.php" class="btn btn-primary-green">
                        <i class="bi bi-shop"></i> Browse Canteens
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="card content-card">
                <div class="card-header">
                    <i class="bi bi-chat-left-text"></i> Your Complaints
                    <span class="badge bg-info ms-2"><?= count($complaints) ?></span>
                </div>
                <div class="card-body">
                    <?php foreach ($complaints as $complaint): ?>
                        <div class="review-item">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <span class="review-author">
                                        <i class="bi bi-building"></i>
                                        <?= htmlspecialchars($complaint['canteen_name']) ?>
                                    </span>
                                    <?php if ($complaint['university_id']): ?>
                                        <small class="text-muted ms-2">(<?= htmlspecialchars($complaint['university_id']) ?>)</small>
                                    <?php endif; ?>
                                    <?php if ($complaint['department']): ?>
                                        <span class="badge bg-info ms-1"><?= htmlspecialchars($complaint['department']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($complaint['created_at']): ?>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> <?= date('M j, Y g:i A', strtotime($complaint['created_at'])) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            <p class="review-comment"><?= htmlspecialchars($complaint['message']) ?></p>
                            <a href="complaint.php?id=<?= $complaint['canteen_id'] ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i> View Details
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
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
