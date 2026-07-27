<?php
/**
 * CampusBite - Complaint Page
 * Students can submit complaints about a canteen
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

// Handle complaint submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = trim($_POST['student_name'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($studentName && strlen($message) >= 10) {
        $stmt = $pdo->prepare(
            'INSERT INTO complaints (canteen_id, student_name, message) VALUES (?, ?, ?)'
        );
        $stmt->execute([$canteenId, $studentName, $message]);

        header('Location: complaint.php?id=' . $canteenId . '&success=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($canteen['name']) ?> - Complaint | CampusBite</title>
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
                    <li class="breadcrumb-item active">Complaint</li>
                </ol>
            </nav>
            <h1 class="mb-2"><i class="bi bi-chat-left-text"></i> Submit a Complaint</h1>
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
                <a class="nav-link" href="review.php?id=<?= $canteenId ?>">
                    <i class="bi bi-star"></i> Reviews
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="complaint.php?id=<?= $canteenId ?>">
                    <i class="bi bi-chat-left-text"></i> Complaint
                </a>
            </li>
        </ul>
    </div>

    <main class="container pb-5">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show auto-dismiss" role="alert">
                <i class="bi bi-check-circle"></i> Your complaint has been submitted. We will look into it soon.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card content-card">
                    <div class="card-header">
                        <i class="bi bi-exclamation-circle"></i> Complaint Form
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            Have an issue with this canteen? Let us know and we'll make sure it gets addressed.
                        </p>

                        <form id="complaintForm" method="POST" action="complaint.php?id=<?= $canteenId ?>">
                            <div class="mb-3">
                                <label for="student_name" class="form-label">Student Name</label>
                                <input type="text" class="form-control" id="student_name" name="student_name"
                                       placeholder="Enter your full name" required maxlength="100">
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">Complaint Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5"
                                          placeholder="Describe your complaint in detail..." required minlength="10"></textarea>
                                <div class="form-text">Minimum 10 characters required.</div>
                            </div>

                            <button type="submit" class="btn btn-primary-green">
                                <i class="bi bi-send"></i> Submit Complaint
                            </button>
                        </form>
                    </div>
                </div>
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
