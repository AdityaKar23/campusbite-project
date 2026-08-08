<?php
/**
 * CampusBite - Register
 * Create a Student or Restaurant Owner account
 */
require_once 'auth.php';

if (isLoggedIn()) {
    redirectByRole();
}

$error = '';
$success = '';
$pdo = getDB();

$canteens = $pdo->query('SELECT canteen_id, name FROM canteens ORDER BY name ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    
    $universityId = trim($_POST['university_id'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';
    $canteenId = filter_input(INPUT_POST, 'canteen_id', FILTER_VALIDATE_INT);

    if ($universityId === '' || $fullName === '' || $password === '' || $confirmPassword === '') {
        $error = 'All fields are required.';
    } elseif (!in_array($role, ['student', 'owner'], true)) {
        $error = 'Please select a valid role.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one number.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif ($role === 'owner' && !$canteenId) {
        $error = 'Restaurant owners must select a canteen.';
    } elseif (findUserByUniversityId($pdo, $universityId)) {
        $error = 'This University ID is already registered.';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        if ($role === 'student') {
            $stmt = $pdo->prepare(
                'INSERT INTO students (university_id, password, full_name) VALUES (?, ?, ?)'
            );
            $stmt->execute([
                $universityId,
                $hashedPassword,
                $fullName,
            ]);
        } elseif ($role === 'owner') {
            $stmt = $pdo->prepare(
                'INSERT INTO canteen_owners (university_id, password, full_name, canteen_id) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                $universityId,
                $hashedPassword,
                $fullName,
                $canteenId,
            ]);
        }

        header('Location: login.php?registered=1');
        exit;
    }
}

$registered = isset($_GET['registered']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | CampusBite</title>
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
            <div class="col-md-8 col-lg-6">
                <div class="card content-card">
                    <div class="card-header text-center">
                        <i class="bi bi-person-plus"></i> Create Account
                    </div>
                    <div class="card-body p-4">
                        <?php if ($registered): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> Registration successful! Please log in.
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="register.php" id="registerForm">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                            <div class="mb-3">
                                <label for="university_id" class="form-label">University ID</label>
                                <input type="text" class="form-control" id="university_id" name="university_id"
                                       placeholder="Enter your University ID" required
                                       value="<?= htmlspecialchars($_POST['university_id'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name"
                                       placeholder="Enter your full name" required maxlength="100"
                                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                            </div>



                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select your role</option>
                                    <option value="student" <?= ($_POST['role'] ?? '') === 'student' ? 'selected' : '' ?>>
                                        Student
                                    </option>
                                    <option value="owner" <?= ($_POST['role'] ?? '') === 'owner' ? 'selected' : '' ?>>
                                        Restaurant Owner
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3" id="canteenField" style="display: none;">
                                <label for="canteen_id" class="form-label">Your Canteen</label>
                                <select class="form-select" id="canteen_id" name="canteen_id">
                                    <option value="">Select a canteen</option>
                                    <?php foreach ($canteens as $canteen): ?>
                                        <option value="<?= (int) $canteen['canteen_id'] ?>"
                                            <?= (int) ($_POST['canteen_id'] ?? 0) === (int) $canteen['canteen_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($canteen['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Min 6 chars, 1 uppercase, 1 number" required minlength="6">
                                <div class="form-text">Must be at least 6 characters with 1 uppercase letter and 1 number.</div>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                       placeholder="Re-enter your password" required minlength="6">
                            </div>

                            <button type="submit" class="btn btn-primary-green w-100">
                                <i class="bi bi-person-plus"></i> Register
                            </button>
                        </form>

                        <p class="text-center text-muted mt-4 mb-0">
                            Already have an account?
                            <a href="login.php">Login here</a>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('role');
            const canteenField = document.getElementById('canteenField');
            const canteenSelect = document.getElementById('canteen_id');

            function toggleCanteenField() {
                const isOwner = roleSelect.value === 'owner';
                canteenField.style.display = isOwner ? 'block' : 'none';
                canteenSelect.required = isOwner;
            }

            roleSelect.addEventListener('change', toggleCanteenField);
            toggleCanteenField();
        });
    </script>
</body>
</html>
