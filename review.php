<?php
/**
 * CampusBite - Reviews Page
 * Students can submit and view canteen reviews
 */
require_once 'auth.php';
requireRole('student');

$user = getCurrentUser();
$canteenId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$success = isset($_GET['success']);
$deleteSuccess = isset($_GET['delete_success']);
$updateSuccess = isset($_GET['update_success']);
$editReviewId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);

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

// Get user's department
$userData = getUserById($pdo, $user['user_id'], $user['user_type']);
$department = $userData['department'] ?? 'N/A';

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $comment = trim($_POST['comment'] ?? '');
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' && $rating >= 1 && $rating <= 5 && strlen($comment) >= 5) {
        $stmt = $pdo->prepare(
            'INSERT INTO reviews (canteen_id, student_id, student_name, university_id, department, rating, comment) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$canteenId, $user['user_id'], $user['full_name'], $user['university_id'], $department, $rating, $comment]);

        header('Location: review.php?id=' . $canteenId . '&success=1');
        exit;
    }
    
    if ($action === 'update' && $rating >= 1 && $rating <= 5 && strlen($comment) >= 5) {
        $reviewId = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
        if (updateReview($pdo, $reviewId, $user['user_id'], $rating, $comment)) {
            header('Location: review.php?id=' . $canteenId . '&update_success=1');
            exit;
        }
    }
    
    if ($action === 'delete') {
        $reviewId = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
        if (deleteReview($pdo, $reviewId, $user['user_id'])) {
            header('Location: review.php?id=' . $canteenId . '&delete_success=1');
            exit;
        }
    }
}

// Fetch all reviews for this canteen
$stmt = $pdo->prepare('SELECT r.*, c.name as canteen_name FROM reviews r JOIN canteens c ON r.canteen_id = c.canteen_id WHERE r.canteen_id = ? ORDER BY r.created_at DESC');
$stmt->execute([$canteenId]);
$reviews = $stmt->fetchAll();

// Get review to edit if edit mode
$editReview = null;
if ($editReviewId) {
    $editReview = getReviewById($pdo, $editReviewId, $user['user_id']);
}

$isOpen = $canteen['status'] === 'open';
$statusClass = $isOpen ? 'status-open' : 'status-closed';
$statusText = $isOpen ? 'Open' : 'Closed';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($canteen['name']) ?> - Reviews | CampusBite</title>
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
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php"><i class="bi bi-person"></i> Profile</a>
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
                    <li class="breadcrumb-item"><a href="menu.php?id=<?= $canteenId ?>"><?= htmlspecialchars($canteen['name']) ?></a></li>
                    <li class="breadcrumb-item active">Reviews</li>
                </ol>
            </nav>
            <h1 class="mb-2"><i class="bi bi-star"></i> Reviews</h1>
            <p class="text-muted"><?= htmlspecialchars($canteen['name']) ?></p>
        </div>
    </section>

    <!-- Canteen Sub-Navigation -->
    <div class="container mb-4">
        <ul class="nav canteen-nav">
            <li class="nav-item">
                <a class="nav-link" href="menu.php?id=<?= $canteenId ?>">
                    <i class="bi bi-menu-button-wide"></i> Menu
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="review.php?id=<?= $canteenId ?>">
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

    <main class="container pb-5">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show auto-dismiss" role="alert">
                <i class="bi bi-check-circle"></i> Thank you! Your review has been submitted successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($updateSuccess): ?>
            <div class="alert alert-success alert-dismissible fade show auto-dismiss" role="alert">
                <i class="bi bi-check-circle"></i> Your review has been updated successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($deleteSuccess): ?>
            <div class="alert alert-success alert-dismissible fade show auto-dismiss" role="alert">
                <i class="bi bi-check-circle"></i> Your review has been deleted successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Write a Review Form -->
        <div class="card content-card">
            <div class="card-header">
                <i class="bi bi-pencil-square"></i> <?= $editReview ? 'Edit Review' : 'Write a Review' ?>
            </div>
            <div class="card-body">
                <form id="reviewForm" method="POST" action="review.php?id=<?= $canteenId ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                    <input type="hidden" name="action" value="<?= $editReview ? 'update' : 'add' ?>">
                    <?php if ($editReview): ?>
                        <input type="hidden" name="review_id" value="<?= $editReview['review_id'] ?>">
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Student Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">University ID</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['university_id']) ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($department) ?>" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="star-input">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" required <?= $editReview && $editReview['rating'] == $i ? 'checked' : '' ?>>
                                <label for="star<?= $i ?>" title="<?= $i ?> stars">
                                    <i class="bi bi-star-fill"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="comment" class="form-label">Your Review</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3"
                                  placeholder="Share your experience..." required minlength="5"><?= $editReview ? htmlspecialchars($editReview['comment']) : '' ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary-green">
                        <i class="bi bi-<?= $editReview ? 'check-circle' : 'send' ?>"></i> <?= $editReview ? 'Update Review' : 'Submit Review' ?>
                    </button>
                    <?php if ($editReview): ?>
                        <a href="review.php?id=<?= $canteenId ?>" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Previous Reviews -->
        <div class="card content-card">
            <div class="card-header">
                <i class="bi bi-chat-quote"></i> Student Reviews
                <span class="badge bg-success ms-2"><?= count($reviews) ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($reviews)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-chat-left-dots display-4"></i>
                        <p class="mt-3">No reviews yet. Be the first to review this canteen!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <span class="review-author">
                                        <i class="bi bi-person-circle"></i>
                                        <?= htmlspecialchars($review['student_name']) ?>
                                    </span>
                                    <?php if ($review['university_id']): ?>
                                        <small class="text-muted">(<?= htmlspecialchars($review['university_id']) ?>)</small>
                                    <?php endif; ?>
                                    <?php if ($review['department']): ?>
                                        <span class="badge bg-info ms-1"><?= htmlspecialchars($review['department']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?= renderStars((int) $review['rating']) ?>
                            </div>
                            <p class="review-comment"><?= htmlspecialchars($review['comment']) ?></p>
                            <?php if ($review['created_at']): ?>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> <?= date('M j, Y g:i A', strtotime($review['created_at'])) ?>
                                </small>
                            <?php endif; ?>
                            <?php if ($review['user_id'] == $user['user_id']): ?>
                                <div class="mt-2">
                                    <a href="review.php?id=<?= $canteenId ?>&edit=<?= $review['review_id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $review['review_id'] ?>">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </div>
                                <!-- Delete Confirmation Modal -->
                                <div class="modal fade" id="deleteModal<?= $review['review_id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Delete Review</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete this review?</p>
                                                <p class="text-muted">This action cannot be undone.</p>
                                                <form method="POST" action="review.php?id=<?= $canteenId ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="review_id" value="<?= $review['review_id'] ?>">
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
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
