<?php
/**
 * CampusBite - Homepage
 * Displays all campus canteens as beautiful cards
 */
require_once 'db.php';

$pdo = getDB();

// Fetch all canteens
$stmt = $pdo->query('SELECT * FROM canteens ORDER BY name ASC');
$canteens = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusBite - Discover Your Campus Canteens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-cup-hot-fill"></i> CampusBite
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="bi bi-house-door"></i> Home</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <div class="hero-icon mb-3">
                <i class="bi bi-shop"></i>
            </div>
            <h1 class="hero-title mb-3">Discover Your Campus Canteens</h1>
            <p class="hero-subtitle">
                Check menus, food availability, prices, reviews, and canteen status in one place.
            </p>
        </div>
    </section>

    <!-- Canteen Cards -->
    <main class="container py-5">
        <div class="row g-4">
            <?php if (empty($canteens)): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <p class="text-muted mt-3">No canteens found. Please import campusbite.sql into phpMyAdmin.</p>
                </div>
            <?php else: ?>
                <?php foreach ($canteens as $canteen): ?>
                    <?php
                    $isOpen = $canteen['status'] === 'open';
                    $statusClass = $isOpen ? 'status-open' : 'status-closed';
                    $statusText = $isOpen ? 'Open' : 'Closed';
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card canteen-card">
                            <div class="card-header">
                                <h5><i class="bi bi-building"></i> <?= htmlspecialchars($canteen['name']) ?></h5>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <p class="location-text mb-3">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <?= htmlspecialchars($canteen['location']) ?>
                                </p>
                                <div class="mb-4">
                                    <span class="status-badge <?= $statusClass ?>">
                                        <span class="status-dot"></span>
                                        <?= $statusText ?>
                                    </span>
                                </div>
                                <a href="menu.php?id=<?= (int) $canteen['canteen_id'] ?>"
                                   class="btn btn-primary-green mt-auto">
                                    <i class="bi bi-menu-button-wide"></i> View Menu
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
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
