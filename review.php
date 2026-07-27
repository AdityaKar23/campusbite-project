<?php
/**
 * CampusBite - Reviews Page
 * Students can submit and view canteen reviews
 */
require_once 'db.php';

$canteenId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$success = isset($_GET['success']);

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

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = trim($_POST['student_name'] ?? '');
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $comment = trim($_POST['comment'] ?? '');

    if ($studentName && $rating >= 1 && $rating <= 5 && strlen($comment) >= 5) {
        $stmt = $pdo->prepare(
            'INSERT INTO reviews (canteen_id, student_name, rating, comment) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$canteenId, $studentName, $rating, $comment]);

        header('Location: review.php?id=' . $canteenId . '&success=1');
        exit;
    }
}

// Fetch all reviews for this canteen
$stmt = $pdo->prepare('SELECT * FROM reviews WHERE canteen_id = ? ORDER BY review_id DESC');
$stmt->execute([$canteenId]);
$reviews = $stmt->fetchAll();

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
                        <a class="nav-link" href="index.php"><i class="bi bi-house-door"></i> Home</a>
                    </li>
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

        <!-- Write a Review Form -->
        <div class="card content-card">
            <div class="card-header">
                <i class="bi bi-pencil-square"></i> Write a Review
            </div>
            <div class="card-body">
                <form id="reviewForm" method="POST" action="review.php?id=<?= $canteenId ?>">
                    <div class="mb-3">
                        <label for="student_name" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="student_name" name="student_name"
                               placeholder="Enter your name" required maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="star-input">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" required>
                                <label for="star<?= $i ?>" title="<?= $i ?> stars">
                                    <i class="bi bi-star-fill"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="comment" class="form-label">Your Review</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3"
                                  placeholder="Share your experience..." required minlength="5"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary-green">
                        <i class="bi bi-send"></i> Submit Review
                    </button>
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
                                <span class="review-author">
                                    <i class="bi bi-person-circle"></i>
                                    <?= htmlspecialchars($review['student_name']) ?>
                                </span>
                                <?= renderStars((int) $review['rating']) ?>
                            </div>
                            <p class="review-comment"><?= htmlspecialchars($review['comment']) ?></p>
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
