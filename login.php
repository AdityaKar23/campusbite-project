<?php
/**
 * CampusBite - Login
 * Authenticate with University ID and password
 */
require_once 'auth.php';

if (isLoggedIn()) {
    redirectByRole();
}

$error = '';
$registered = isset($_GET['registered']);
$timeout = isset($_GET['timeout']);
$passwordChanged = isset($_GET['password_changed']);
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    
    $universityId = trim($_POST['university_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($universityId === '' || $password === '') {
        $error = 'Please enter your University ID and password.';
    } else {
        $user = findUserByUniversityId($pdo, $universityId);

        if ($user && password_verify($password, $user['password'])) {
            loginUser($user);
            redirectByRole();
        } else {
            $error = 'Invalid University ID or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CampusBite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="login.php">
                <i class="bi bi-cup-hot-fill"></i> CampusBite
            </a>
        </div>
    </nav>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card content-card">
                    <div class="card-header text-center">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </div>
                    <div class="card-body p-4">
                        <?php if ($registered): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> Registration successful! Please log in.
                            </div>
                        <?php endif; ?>

                        <?php if ($timeout): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-clock"></i> Your session has expired due to inactivity. Please log in again.
                            </div>
                        <?php endif; ?>

                        <?php if ($passwordChanged): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> Password changed successfully! Please log in with your new password.
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="login.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                            <div class="mb-3">
                                <label for="university_id" class="form-label">University ID</label>
                                <input type="text" class="form-control" id="university_id" name="university_id"
                                       placeholder="Enter your University ID" required
                                       value="<?= htmlspecialchars($_POST['university_id'] ?? '') ?>">
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Enter your password" required>
                            </div>

                            <button type="submit" class="btn btn-primary-green w-100">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </form>

                        <p class="text-center text-muted mt-4 mb-0">
                            Don't have an account?
                            <a href="register.php">Register here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer text-center">
        <div class="container">
            <p><strong>CampusBite</strong> &copy; 2026</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
