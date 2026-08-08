<?php
/**
 * CampusBite - Search Page
 * Search for food items and canteens
 */
require_once 'auth.php';
requireRole('student');

$user = getCurrentUser();
$pdo = getDB();

$searchQuery = trim($_GET['q'] ?? '');
$searchType = $_GET['type'] ?? 'food'; // 'food' or 'canteen'
$results = [];
$hasSearched = !empty($searchQuery);

if ($hasSearched) {
    if ($searchType === 'food') {
        $results = searchFood($pdo, $searchQuery);
    } else {
        $results = searchCanteens($pdo, $searchQuery);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search | CampusBite</title>
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
                        <a class="nav-link active" href="search.php"><i class="bi bi-search"></i> Search</a>
                    </li>
                    <?php require 'includes/nav_auth.php'; ?>
                </ul>
            </div>
        </div>
    </nav>

    <section class="page-header">
        <div class="container">
            <h1 class="mb-2"><i class="bi bi-search"></i> Search</h1>
            <p class="text-muted mb-0">Find food items and canteens across campus</p>
        </div>
    </section>

    <main class="container pb-5">
        <!-- Search Form -->
        <div class="card content-card mb-4">
            <div class="card-body">
                <form method="GET" action="search.php">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control" id="searchQuery" name="q"
                                       placeholder="Search for food or canteens..." 
                                       value="<?= htmlspecialchars($searchQuery) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="type" id="searchType">
                                <option value="food" <?= $searchType === 'food' ? 'selected' : '' ?>>Food Items</option>
                                <option value="canteen" <?= $searchType === 'canteen' ? 'selected' : '' ?>>Canteens</option>
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

        <!-- Search Results -->
        <?php if ($hasSearched): ?>
            <div class="card content-card">
                <div class="card-header">
                    <i class="bi bi-list-ul"></i> Search Results
                    <span class="badge bg-primary ms-2"><?= count($results) ?> found</span>
                </div>
                <div class="card-body">
                    <?php if (empty($results)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-search display-1"></i>
                            <p class="mt-3">No results found for "<?= htmlspecialchars($searchQuery) ?>"</p>
                            <p class="small">Try different keywords or search for something else.</p>
                        </div>
                    <?php else: ?>
                        <?php if ($searchType === 'food'): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Food Name</th>
                                            <th>Canteen</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Availability</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results as $item): ?>
                                            <tr>
                                                <td>
                                                    <i class="bi bi-egg-fried text-muted me-1"></i>
                                                    <?= htmlspecialchars($item['food_name']) ?>
                                                </td>
                                                <td>
                                                    <a href="menu.php?id=<?= $item['canteen_id'] ?>" class="text-decoration-none">
                                                        <?= htmlspecialchars($item['canteen_name']) ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="price-tag">৳<?= number_format($item['price'], 2) ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?= $item['stock'] ?? 0 ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($item['available']): ?>
                                                        <span class="badge bg-success">Available</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Out of Stock</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="menu.php?id=<?= $item['canteen_id'] ?>" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-eye"></i> View Menu
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="row g-4">
                                <?php foreach ($results as $canteen): ?>
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
                                                <a href="menu.php?id=<?= $canteen['canteen_id'] ?>"
                                                   class="btn btn-primary-green mt-auto">
                                                    <i class="bi bi-menu-button-wide"></i> View Menu
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Search Tips -->
            <div class="card content-card">
                <div class="card-header">
                    <i class="bi bi-lightbulb"></i> Search Tips
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6><i class="bi bi-egg-fried"></i> Search Food Items</h6>
                            <ul class="list-unstyled text-muted">
                                <li>• Search by food name (e.g., "biryani", "burger")</li>
                                <li>• Results show price, stock, and availability</li>
                                <li>• Click "View Menu" to see complete canteen menu</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="bi bi-building"></i> Search Canteens</h6>
                            <ul class="list-unstyled text-muted">
                                <li>• Search by canteen name or location</li>
                                <li>• Results show canteen status and location</li>
                                <li>• Click to view complete menu and details</li>
                            </ul>
                        </div>
                    </div>
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
