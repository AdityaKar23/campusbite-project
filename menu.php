<?php
/**
 * CampusBite - Menu Page
 * Displays food menu for a selected canteen
 */
require_once 'auth.php';
requireRole('student');

// Validate canteen ID
$canteenId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$canteenId) {
    header('Location: index.php');
    exit;
}

$pdo = getDB();
$canteen = getCanteen($pdo, $canteenId);

if (!$canteen) {
    header('Location: index.php');
    exit;
}

// Fetch menu items for this canteen
$stmt = $pdo->prepare('SELECT * FROM food WHERE canteen_id = ? ORDER BY food_name ASC');
$stmt->execute([$canteenId]);
$foods = $stmt->fetchAll();

$isOpen = $canteen['status'] === 'open';
$statusClass = $isOpen ? 'status-open' : 'status-closed';
$statusText = $isOpen ? 'Open' : 'Closed';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($canteen['name']) ?> - Menu | CampusBite</title>
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
                        <a class="nav-link" href="student_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-house-door"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="search.php"><i class="bi bi-search"></i> Search</a>
                    </li>
                    <?php require 'includes/nav_auth.php'; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($canteen['name']) ?></li>
                </ol>
            </nav>
            <h1 class="mb-2"><i class="bi bi-building"></i> <?= htmlspecialchars($canteen['name']) ?></h1>
            <p class="text-muted mb-2">
                <i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($canteen['location']) ?>
            </p>
            <span class="status-badge <?= $statusClass ?>">
                <span class="status-dot"></span>
                <?= $statusText ?>
            </span>
        </div>
    </section>

    <!-- Canteen Sub-Navigation -->
    <div class="container mb-4">
        <ul class="nav canteen-nav">
            <li class="nav-item">
                <a class="nav-link active" href="menu.php?id=<?= $canteenId ?>">
                    <i class="bi bi-menu-button-wide"></i> Menu
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="review.php?id=<?= $canteenId ?>">
                    <i class="bi bi-star"></i> Reviews
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="complaint.php?id=<?= $canteenId ?>">
                    <i class="bi bi-chat-left-text"></i> Complaint
                </a>
            </li>
        </ul>
    </div>

    <!-- Menu Table -->
    <main class="container pb-5">
        <div class="card content-card">
            <div class="card-header">
                <i class="bi bi-list-ul"></i> Food Menu
            </div>
            <div class="card-body p-0">
                <?php if (empty($foods)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-emoji-frown display-4"></i>
                        <p class="mt-3">No menu items available for this canteen.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table menu-table">
                            <thead>
                                <tr>
                                    <th>Food Name</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Availability</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($foods as $food): ?>
                                    <tr>
                                        <td>
                                            <i class="bi bi-egg-fried text-muted me-1"></i>
                                            <?= htmlspecialchars($food['food_name']) ?>
                                        </td>
                                        <td>
                                            <span class="price-tag">৳<?= number_format($food['price'], 2) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= $food['stock'] ?? 0 ?> in stock</span>
                                        </td>
                                        <td>
                                            <?php if ($food['available']): ?>
                                                <span class="availability-badge availability-available">
                                                    🟢 Available
                                                </span>
                                            <?php else: ?>
                                                <span class="availability-badge availability-unavailable">
                                                    🔴 Out of Stock
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
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
