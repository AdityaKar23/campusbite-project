<?php
/**
 * CampusBite - Owner Dashboard
 * Landing page for restaurant owners
 */
require_once 'auth.php';
requireRole('owner');

$user = getCurrentUser();
$pdo = getDB();
$csrfToken = generateCsrfToken();

$canteenId = (int) $user['canteen_id'];
$canteen = $canteenId ? getCanteen($pdo, $canteenId) : null;

if (!$canteen) {
    die('<div class="alert alert-danger m-4">No canteen assigned to your account. Please contact an administrator.</div>');
}

$statusUpdated = false;
$foodMessage = '';
$foodMessageType = '';

// Handle canteen status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    requireCsrfToken();
    
    $newStatus = $canteen['status'] === 'open' ? 'closed' : 'open';
    $stmt = $pdo->prepare('UPDATE canteens SET status = ? WHERE canteen_id = ?');
    $stmt->execute([$newStatus, $canteenId]);
    $canteen['status'] = $newStatus;
    $statusUpdated = true;
}

// Handle food management operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    
    // Add new food
    if (isset($_POST['add_food'])) {
        $foodName = trim($_POST['food_name'] ?? '');
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT) ?? 0;
        $available = isset($_POST['available']);
        
        if ($foodName && $price > 0) {
            if (addFood($pdo, $canteenId, $foodName, $price, $stock, $available)) {
                $foodMessage = 'Food item added successfully!';
                $foodMessageType = 'success';
            } else {
                $foodMessage = 'Failed to add food item.';
                $foodMessageType = 'danger';
            }
        } else {
            $foodMessage = 'Please enter a valid food name and price.';
            $foodMessageType = 'warning';
        }
    }
    
    // Update food
    if (isset($_POST['update_food'])) {
        $foodId = filter_input(INPUT_POST, 'food_id', FILTER_VALIDATE_INT);
        $foodName = trim($_POST['food_name'] ?? '');
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT) ?? 0;
        $available = isset($_POST['available']);
        
        if ($foodId && $foodName && $price > 0) {
            $food = getFoodById($pdo, $foodId, $canteenId);
            if ($food) {
                if (updateFood($pdo, $foodId, $foodName, $price, $stock, $available)) {
                    $foodMessage = 'Food item updated successfully!';
                    $foodMessageType = 'success';
                } else {
                    $foodMessage = 'Failed to update food item.';
                    $foodMessageType = 'danger';
                }
            } else {
                $foodMessage = 'Food item not found or access denied.';
                $foodMessageType = 'danger';
            }
        } else {
            $foodMessage = 'Please enter valid food details.';
            $foodMessageType = 'warning';
        }
    }
    
    // Delete food
    if (isset($_POST['delete_food'])) {
        $foodId = filter_input(INPUT_POST, 'food_id', FILTER_VALIDATE_INT);
        
        if ($foodId) {
            $food = getFoodById($pdo, $foodId, $canteenId);
            if ($food) {
                if (deleteFood($pdo, $foodId)) {
                    $foodMessage = 'Food item deleted successfully!';
                    $foodMessageType = 'success';
                } else {
                    $foodMessage = 'Failed to delete food item.';
                    $foodMessageType = 'danger';
                }
            } else {
                $foodMessage = 'Food item not found or access denied.';
                $foodMessageType = 'danger';
            }
        }
    }
    
    // Update price
    if (isset($_POST['update_price'])) {
        $foodId = filter_input(INPUT_POST, 'food_id', FILTER_VALIDATE_INT);
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        
        if ($foodId && $price > 0) {
            $food = getFoodById($pdo, $foodId, $canteenId);
            if ($food) {
                if (updateFoodPrice($pdo, $foodId, $price)) {
                    $foodMessage = 'Price updated successfully!';
                    $foodMessageType = 'success';
                } else {
                    $foodMessage = 'Failed to update price.';
                    $foodMessageType = 'danger';
                }
            } else {
                $foodMessage = 'Food item not found or access denied.';
                $foodMessageType = 'danger';
            }
        } else {
            $foodMessage = 'Please enter a valid price.';
            $foodMessageType = 'warning';
        }
    }
    
    // Toggle availability
    if (isset($_POST['toggle_availability'])) {
        $foodId = filter_input(INPUT_POST, 'food_id', FILTER_VALIDATE_INT);
        
        if ($foodId) {
            $food = getFoodById($pdo, $foodId, $canteenId);
            if ($food) {
                if (toggleFoodAvailability($pdo, $foodId)) {
                    $foodMessage = 'Availability updated successfully!';
                    $foodMessageType = 'success';
                } else {
                    $foodMessage = 'Failed to update availability.';
                    $foodMessageType = 'danger';
                }
            } else {
                $foodMessage = 'Food item not found or access denied.';
                $foodMessageType = 'danger';
            }
        }
    }
    
    // Increase stock
    if (isset($_POST['increase_stock'])) {
        $foodId = filter_input(INPUT_POST, 'food_id', FILTER_VALIDATE_INT);
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_INT) ?? 1;
        
        if ($foodId && $amount > 0) {
            $food = getFoodById($pdo, $foodId, $canteenId);
            if ($food) {
                if (increaseFoodStock($pdo, $foodId, $amount)) {
                    $foodMessage = "Stock increased by {$amount} successfully!";
                    $foodMessageType = 'success';
                } else {
                    $foodMessage = 'Failed to increase stock.';
                    $foodMessageType = 'danger';
                }
            } else {
                $foodMessage = 'Food item not found or access denied.';
                $foodMessageType = 'danger';
            }
        }
    }
    
    // Decrease stock
    if (isset($_POST['decrease_stock'])) {
        $foodId = filter_input(INPUT_POST, 'food_id', FILTER_VALIDATE_INT);
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_INT) ?? 1;
        
        if ($foodId && $amount > 0) {
            $food = getFoodById($pdo, $foodId, $canteenId);
            if ($food) {
                if (decreaseFoodStock($pdo, $foodId, $amount)) {
                    $foodMessage = "Stock decreased by {$amount} successfully!";
                    $foodMessageType = 'success';
                } else {
                    $foodMessage = 'Failed to decrease stock.';
                    $foodMessageType = 'danger';
                }
            } else {
                $foodMessage = 'Food item not found or access denied.';
                $foodMessageType = 'danger';
            }
        }
    }
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM food WHERE canteen_id = ?');
$stmt->execute([$canteenId]);
$menuCount = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE canteen_id = ?');
$stmt->execute([$canteenId]);
$reviewCount = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM complaints WHERE canteen_id = ?');
$stmt->execute([$canteenId]);
$complaintCount = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT c.*, s.university_id, s.department FROM complaints c LEFT JOIN students s ON c.student_id = s.student_id WHERE c.canteen_id = ? ORDER BY c.created_at DESC LIMIT 5');
$stmt->execute([$canteenId]);
$recentComplaints = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT r.*, s.university_id, s.department FROM reviews r LEFT JOIN students s ON r.student_id = s.student_id WHERE r.canteen_id = ? ORDER BY r.created_at DESC LIMIT 5');
$stmt->execute([$canteenId]);
$recentReviews = $stmt->fetchAll();

// Fetch all food items for this canteen
$foodItems = getFoodByCanteen($pdo, $canteenId);

$isOpen = $canteen['status'] === 'open';
$statusClass = $isOpen ? 'status-open' : 'status-closed';
$statusText = $isOpen ? 'Open' : 'Closed';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard | CampusBite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="owner_dashboard.php">
                <i class="bi bi-cup-hot-fill"></i> CampusBite
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="owner_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <?php require 'includes/nav_auth.php'; ?>
                </ul>
            </div>
        </div>
    </nav>

    <section class="page-header">
        <div class="container">
            <h1 class="mb-2"><i class="bi bi-shop"></i> Owner Dashboard</h1>
            <p class="text-muted mb-2">Welcome, <?= htmlspecialchars($user['full_name']) ?></p>
            <h5 class="mb-2"><i class="bi bi-building"></i> <?= htmlspecialchars($canteen['name']) ?></h5>
            <span class="status-badge <?= $statusClass ?>">
                <span class="status-dot"></span>
                <?= $statusText ?>
            </span>
        </div>
    </section>

    <main class="container pb-5">
        <?php if ($statusUpdated): ?>
            <div class="alert alert-success alert-dismissible fade show auto-dismiss" role="alert">
                <i class="bi bi-check-circle"></i> Canteen status updated successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($foodMessage): ?>
            <div class="alert alert-<?= $foodMessageType ?> alert-dismissible fade show auto-dismiss" role="alert">
                <i class="bi bi-<?= $foodMessageType === 'success' ? 'check-circle' : ($foodMessageType === 'danger' ? 'exclamation-triangle' : 'info-circle') ?>"></i>
                <?= htmlspecialchars($foodMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card content-card text-center p-4">
                    <i class="bi bi-list-ul display-4 text-success"></i>
                    <h3 class="mt-3 mb-0"><?= $menuCount ?></h3>
                    <p class="text-muted mb-0">Menu Items</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card content-card text-center p-4">
                    <i class="bi bi-star display-4 text-warning"></i>
                    <h3 class="mt-3 mb-0"><?= $reviewCount ?></h3>
                    <p class="text-muted mb-0">Reviews</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card content-card text-center p-4">
                    <i class="bi bi-chat-left-text display-4 text-danger"></i>
                    <h3 class="mt-3 mb-0"><?= $complaintCount ?></h3>
                    <p class="text-muted mb-0">Complaints</p>
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <i class="bi bi-toggle-on"></i> Canteen Status
            </div>
            <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                <p class="mb-0 text-muted">
                    Current status: <strong><?= $statusText ?></strong>
                    &mdash; <?= htmlspecialchars($canteen['location']) ?>
                </p>
                <form method="POST" action="owner_dashboard.php">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                    <input type="hidden" name="toggle_status" value="1">
                    <button type="submit" class="btn btn-primary-green">
                        <i class="bi bi-arrow-repeat"></i>
                        Mark as <?= $isOpen ? 'Closed' : 'Open' ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Food Management Section -->
        <div class="card content-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-list-ul"></i> Food Menu Management
                </div>
                <button class="btn btn-sm btn-primary-green" type="button" data-bs-toggle="collapse" data-bs-target="#addFoodForm">
                    <i class="bi bi-plus-circle"></i> Add New Food
                </button>
            </div>
            <div class="card-body">
                <!-- Add Food Form (Collapsible) -->
                <div class="collapse mb-4" id="addFoodForm">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="bi bi-plus-circle"></i> Add New Food Item</h6>
                            <form method="POST" action="owner_dashboard.php">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                                <input type="hidden" name="add_food" value="1">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="new_food_name" class="form-label">Food Name</label>
                                        <input type="text" class="form-control" id="new_food_name" name="food_name" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="new_price" class="form-label">Price (৳)</label>
                                        <input type="number" class="form-control" id="new_price" name="price" step="0.01" min="0" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="new_stock" class="form-label">Stock</label>
                                        <input type="number" class="form-control" id="new_stock" name="stock" min="0" value="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Availability</label>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" id="new_available" name="available" checked>
                                            <label class="form-check-label" for="new_available">Available</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary-green">
                                            <i class="bi bi-plus-circle"></i> Add Food Item
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Food List -->
                <?php if (empty($foodItems)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-emoji-frown display-4"></i>
                        <p class="mt-3">No food items yet. Add your first item above!</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Food Name</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Availability</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($foodItems as $food): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($food['food_name']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="price-tag">৳<?= number_format($food['price'], 2) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?= $food['stock'] ?? 0 ?></span>
                                        </td>
                                        <td>
                                            <?php if ($food['available']): ?>
                                                <span class="badge bg-success">Available</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <!-- Edit Button -->
                                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editFoodModal<?= $food['food_id'] ?>" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <!-- Increase Stock -->
                                                <form method="POST" action="owner_dashboard.php" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                                                    <input type="hidden" name="increase_stock" value="1">
                                                    <input type="hidden" name="food_id" value="<?= $food['food_id'] ?>">
                                                    <input type="hidden" name="amount" value="10">
                                                    <button type="submit" class="btn btn-outline-success" title="Increase Stock (+10)">
                                                        <i class="bi bi-plus-circle"></i>
                                                    </button>
                                                </form>
                                                <!-- Decrease Stock -->
                                                <form method="POST" action="owner_dashboard.php" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                                                    <input type="hidden" name="decrease_stock" value="1">
                                                    <input type="hidden" name="food_id" value="<?= $food['food_id'] ?>">
                                                    <input type="hidden" name="amount" value="10">
                                                    <button type="submit" class="btn btn-outline-warning" title="Decrease Stock (-10)">
                                                        <i class="bi bi-dash-circle"></i>
                                                    </button>
                                                </form>
                                                <!-- Toggle Availability -->
                                                <form method="POST" action="owner_dashboard.php" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                                                    <input type="hidden" name="toggle_availability" value="1">
                                                    <input type="hidden" name="food_id" value="<?= $food['food_id'] ?>">
                                                    <button type="submit" class="btn btn-outline-<?= $food['available'] ? 'secondary' : 'info' ?>" title="<?= $food['available'] ? 'Mark Out of Stock' : 'Mark Available' ?>">
                                                        <i class="bi bi-<?= $food['available'] ? 'eye-slash' : 'eye' ?>"></i>
                                                    </button>
                                                </form>
                                                <!-- Delete Button -->
                                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteFoodModal<?= $food['food_id'] ?>" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editFoodModal<?= $food['food_id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Food Item</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST" action="owner_dashboard.php">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                                                        <input type="hidden" name="update_food" value="1">
                                                        <input type="hidden" name="food_id" value="<?= $food['food_id'] ?>">
                                                        <div class="mb-3">
                                                            <label for="edit_food_name_<?= $food['food_id'] ?>" class="form-label">Food Name</label>
                                                            <input type="text" class="form-control" id="edit_food_name_<?= $food['food_id'] ?>" name="food_name" value="<?= htmlspecialchars($food['food_name']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="edit_price_<?= $food['food_id'] ?>" class="form-label">Price (৳)</label>
                                                            <input type="number" class="form-control" id="edit_price_<?= $food['food_id'] ?>" name="price" step="0.01" min="0" value="<?= $food['price'] ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="edit_stock_<?= $food['food_id'] ?>" class="form-label">Stock</label>
                                                            <input type="number" class="form-control" id="edit_stock_<?= $food['food_id'] ?>" name="stock" min="0" value="<?= $food['stock'] ?? 0 ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" id="edit_available_<?= $food['food_id'] ?>" name="available" <?= $food['available'] ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="edit_available_<?= $food['food_id'] ?>">Available</label>
                                                            </div>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary-green">
                                                            <i class="bi bi-check-circle"></i> Update Food Item
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Delete Confirmation Modal -->
                                    <div class="modal fade" id="deleteFoodModal<?= $food['food_id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Delete Food Item</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete <strong><?= htmlspecialchars($food['food_name']) ?></strong>?</p>
                                                    <p class="text-muted">This action cannot be undone.</p>
                                                    <form method="POST" action="owner_dashboard.php">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                                                        <input type="hidden" name="delete_food" value="1">
                                                        <input type="hidden" name="food_id" value="<?= $food['food_id'] ?>">
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card content-card">
                    <div class="card-header">
                        <i class="bi bi-chat-left-text"></i> Recent Complaints
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentComplaints)): ?>
                            <p class="text-muted mb-0">No complaints yet.</p>
                        <?php else: ?>
                            <?php foreach ($recentComplaints as $complaint): ?>
                                <div class="review-item">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                        <div>
                                            <span class="review-author">
                                                <i class="bi bi-person-circle"></i>
                                                <?= htmlspecialchars($complaint['student_name']) ?>
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
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card content-card">
                    <div class="card-header">
                        <i class="bi bi-star"></i> Recent Reviews
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentReviews)): ?>
                            <p class="text-muted mb-0">No reviews yet.</p>
                        <?php else: ?>
                            <?php foreach ($recentReviews as $review): ?>
                                <div class="review-item">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                        <div>
                                            <span class="review-author">
                                                <i class="bi bi-person-circle"></i>
                                                <?= htmlspecialchars($review['student_name']) ?>
                                            </span>
                                            <?php if ($review['university_id']): ?>
                                                <small class="text-muted ms-2">(<?= htmlspecialchars($review['university_id']) ?>)</small>
                                            <?php endif; ?>
                                            <?php if ($review['department']): ?>
                                                <span class="badge bg-info ms-1"><?= htmlspecialchars($review['department']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <?= renderStars((int) $review['rating']) ?>
                                            <?php if ($review['created_at']): ?>
                                                <small class="text-muted ms-2">
                                                    <i class="bi bi-clock"></i> <?= date('M j, Y g:i A', strtotime($review['created_at'])) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <p class="review-comment"><?= htmlspecialchars($review['comment'] ?? $review['message'] ?? '') ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer text-center">
        <div class="container">
            <p><strong>CampusBite</strong> &copy; 2026</p>
            <p>Restaurant Owner Portal</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
