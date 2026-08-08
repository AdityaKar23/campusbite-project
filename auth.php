<?php
/**
 * CampusBite - Authentication helpers
 * Session-based auth with student and owner roles
 * Enhanced with CSRF protection and session security
 */

require_once 'db.php';

function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_secure' => isset($_SERVER['HTTPS']),
            'cookie_httponly' => true,
            'cookie_samesite' => 'Strict',
            'use_strict_mode' => true,
            'use_cookies' => true,
            'use_only_cookies' => true,
        ]);
    }
    
    // Session timeout (30 minutes of inactivity)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        session_unset();
        session_destroy();
        header('Location: login.php?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
    
    // Regenerate session ID periodically (every 15 minutes)
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 900) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

function isLoggedIn(): bool
{
    startSession();
    return isset($_SESSION['user_id']);
}

function getCurrentUser(): ?array
{
    startSession();

    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    return [
        'user_id'       => $_SESSION['user_id'],
        'university_id' => $_SESSION['university_id'],
        'full_name'     => $_SESSION['full_name'],
        'role'          => $_SESSION['role'],
        'canteen_id'    => $_SESSION['canteen_id'] ?? null,
        'user_type'     => $_SESSION['user_type'] ?? null,
    ];
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole(string $role): void
{
    requireLogin();
    $user = getCurrentUser();

    if ($user['role'] !== $role) {
        if ($user['role'] === 'student') {
            header('Location: student_dashboard.php');
        } else {
            header('Location: owner_dashboard.php');
        }
        exit;
    }
}

function loginUser(array $user): void
{
    startSession();
    
    // Regenerate session ID on login to prevent session fixation
    session_regenerate_id(true);
    
    $_SESSION['user_id']       = $user['user_id'];
    $_SESSION['university_id'] = $user['university_id'];
    $_SESSION['full_name']     = $user['full_name'];
    $_SESSION['role']          = $user['role'];
    $_SESSION['canteen_id']    = $user['canteen_id'] ?? null;
    $_SESSION['user_type']     = $user['user_type'] ?? null;
    $_SESSION['created']       = time(); // Reset session creation time
    $_SESSION['last_activity'] = time(); // Reset activity timer
}

function logoutUser(): void
{
    startSession();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

function redirectByRole(): void
{
    $user = getCurrentUser();

    if ($user['role'] === 'student') {
        header('Location: student_dashboard.php');
    } else {
        header('Location: owner_dashboard.php');
    }
    exit;
}

function findUserByUniversityId(PDO $pdo, string $universityId): ?array
{
    // First check students table
    $stmt = $pdo->prepare('SELECT * FROM students WHERE university_id = ?');
    $stmt->execute([$universityId]);
    $user = $stmt->fetch();

    if ($user) {
        $user['role'] = 'student';
        $user['user_type'] = 'student';
        $user['user_id'] = $user['student_id'];
        return $user;
    }

    // Then check canteen_owners table
    $stmt = $pdo->prepare('SELECT * FROM canteen_owners WHERE university_id = ?');
    $stmt->execute([$universityId]);
    $user = $stmt->fetch();

    if ($user) {
        $user['role'] = 'owner';
        $user['user_type'] = 'owner';
        $user['user_id'] = $user['owner_id'];
        return $user;
    }

    return null;
}

/**
 * CSRF Protection Functions
 */
function generateCsrfToken(): string
{
    startSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken(string $token): bool
{
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function requireCsrfToken(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!validateCsrfToken($token)) {
            startSession();
            session_unset();
            session_destroy();
            die('<div class="alert alert-danger m-4">Invalid CSRF token. Please try again.</div>');
        }
    }
}

/**
 * Validate University ID and Name against official IUB records
 * Returns array with 'valid' (bool) and 'record' (array|null)
 */
function validateOfficialRecords(string $universityId, string $fullName, string $role): array
{
    // Official IUB records
    $officialRecords = [
        'student' => [
            '2430901' => ['name' => 'Student 1'],
            '2430109' => ['name' => 'Student 2'],
            '2340888' => ['name' => 'Student 3'],
            '2430876' => ['name' => 'Student 4'],
        ],
        'owner' => [
            '4006' => ['name' => 'Owner 1', 'restaurant' => 'Campus Cafe'],
            '5678' => ['name' => 'Owner 2', 'restaurant' => 'Food Corner'],
            '7865' => ['name' => 'Owner 3', 'restaurant' => 'Green Canteen'],
        ]
    ];

    // Check if University ID exists in official records for the given role
    if (isset($officialRecords[$role][$universityId])) {
        $record = $officialRecords[$role][$universityId];
        
        // Case-insensitive name comparison
        if (strcasecmp(trim($fullName), trim($record['name'])) === 0) {
            return ['valid' => true, 'record' => $record];
        }
    }

    return ['valid' => false, 'record' => null];
}

/**
 * Food Management Functions
 */

/**
 * Add a new food item to a canteen
 */
function addFood(PDO $pdo, int $canteenId, string $foodName, float $price, int $stock = 0, bool $available = true): bool
{
    $stmt = $pdo->prepare(
        'INSERT INTO food (canteen_id, food_name, price, stock, available) VALUES (?, ?, ?, ?, ?)'
    );
    return $stmt->execute([$canteenId, $foodName, $price, $stock, $available ? 1 : 0]);
}

/**
 * Update an existing food item
 */
function updateFood(PDO $pdo, int $foodId, string $foodName, float $price, int $stock, bool $available): bool
{
    $stmt = $pdo->prepare(
        'UPDATE food SET food_name = ?, price = ?, stock = ?, available = ? WHERE food_id = ?'
    );
    return $stmt->execute([$foodName, $price, $stock, $available ? 1 : 0, $foodId]);
}

/**
 * Delete a food item
 */
function deleteFood(PDO $pdo, int $foodId): bool
{
    $stmt = $pdo->prepare('DELETE FROM food WHERE food_id = ?');
    return $stmt->execute([$foodId]);
}

/**
 * Update food price
 */
function updateFoodPrice(PDO $pdo, int $foodId, float $price): bool
{
    $stmt = $pdo->prepare('UPDATE food SET price = ? WHERE food_id = ?');
    return $stmt->execute([$price, $foodId]);
}

/**
 * Toggle food availability
 */
function toggleFoodAvailability(PDO $pdo, int $foodId): bool
{
    $stmt = $pdo->prepare('UPDATE food SET available = NOT available WHERE food_id = ?');
    return $stmt->execute([$foodId]);
}

/**
 * Get food item by ID (ensures it belongs to the owner's canteen)
 */
function getFoodById(PDO $pdo, int $foodId, int $canteenId): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM food WHERE food_id = ? AND canteen_id = ?');
    $stmt->execute([$foodId, $canteenId]);
    $food = $stmt->fetch();
    return $food ?: null;
}

/**
 * Get all food items for a canteen
 */
function getFoodByCanteen(PDO $pdo, int $canteenId): array
{
    $stmt = $pdo->prepare('SELECT * FROM food WHERE canteen_id = ? ORDER BY food_name ASC');
    $stmt->execute([$canteenId]);
    return $stmt->fetchAll();
}

/**
 * Update food stock
 */
function updateFoodStock(PDO $pdo, int $foodId, int $stock): bool
{
    $stmt = $pdo->prepare('UPDATE food SET stock = ? WHERE food_id = ?');
    return $stmt->execute([$stock, $foodId]);
}

/**
 * Increase food stock
 */
function increaseFoodStock(PDO $pdo, int $foodId, int $amount = 1): bool
{
    $stmt = $pdo->prepare('UPDATE food SET stock = stock + ? WHERE food_id = ?');
    return $stmt->execute([$amount, $foodId]);
}

/**
 * Decrease food stock
 */
function decreaseFoodStock(PDO $pdo, int $foodId, int $amount = 1): bool
{
    $stmt = $pdo->prepare('UPDATE food SET stock = GREATEST(0, stock - ?) WHERE food_id = ?');
    return $stmt->execute([$amount, $foodId]);
}

/**
 * Search functionality
 */

/**
 * Search food items across all canteens
 */
function searchFood(PDO $pdo, string $query): array
{
    $stmt = $pdo->prepare(
        "SELECT f.*, c.name as canteen_name, c.location, c.status 
         FROM food f 
         JOIN canteens c ON f.canteen_id = c.canteen_id 
         WHERE f.food_name LIKE ? 
         ORDER BY f.food_name ASC"
    );
    $stmt->execute(['%' . $query . '%']);
    return $stmt->fetchAll();
}

/**
 * Search canteens by name or location
 */
function searchCanteens(PDO $pdo, string $query): array
{
    $stmt = $pdo->prepare(
        "SELECT * FROM canteens 
         WHERE name LIKE ? OR location LIKE ? 
         ORDER BY name ASC"
    );
    $stmt->execute(['%' . $query . '%', '%' . $query . '%']);
    return $stmt->fetchAll();
}

/**
 * User Profile Management
 */

/**
 * Update user profile information
 */
function updateUserProfile(PDO $pdo, int $userId, string $fullName, string $userType): bool
{
    if ($userType === 'student') {
        $stmt = $pdo->prepare('UPDATE students SET full_name = ? WHERE student_id = ?');
        return $stmt->execute([$fullName, $userId]);
    } elseif ($userType === 'owner') {
        $stmt = $pdo->prepare('UPDATE canteen_owners SET full_name = ? WHERE owner_id = ?');
        return $stmt->execute([$fullName, $userId]);
    }
    return false;
}

/**
 * Change user password
 */
function changeUserPassword(PDO $pdo, int $userId, string $newPassword, string $userType): bool
{
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    if ($userType === 'student') {
        $stmt = $pdo->prepare('UPDATE students SET password = ? WHERE student_id = ?');
        return $stmt->execute([$hashedPassword, $userId]);
    } elseif ($userType === 'owner') {
        $stmt = $pdo->prepare('UPDATE canteen_owners SET password = ? WHERE owner_id = ?');
        return $stmt->execute([$hashedPassword, $userId]);
    }
    return false;
}

/**
 * Verify current password before changing
 */
function verifyCurrentPassword(PDO $pdo, int $userId, string $currentPassword, string $userType): bool
{
    if ($userType === 'student') {
        $stmt = $pdo->prepare('SELECT password FROM students WHERE student_id = ?');
    } elseif ($userType === 'owner') {
        $stmt = $pdo->prepare('SELECT password FROM canteen_owners WHERE owner_id = ?');
    } else {
        return false;
    }
    
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if ($user) {
        return password_verify($currentPassword, $user['password']);
    }
    return false;
}

/**
 * Get user by ID
 */
function getUserById(PDO $pdo, int $userId, string $userType): ?array
{
    if ($userType === 'student') {
        $stmt = $pdo->prepare('SELECT * FROM students WHERE student_id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if ($user) {
            $user['role'] = 'student';
            $user['user_type'] = 'student';
            $user['user_id'] = $user['student_id'];
            return $user;
        }
    } elseif ($userType === 'owner') {
        $stmt = $pdo->prepare('SELECT * FROM canteen_owners WHERE owner_id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if ($user) {
            $user['role'] = 'owner';
            $user['user_type'] = 'owner';
            $user['user_id'] = $user['owner_id'];
            return $user;
        }
    }
    return null;
}

/**
 * Review and Complaint Management Functions
 */

/**
 * Get reviews by user ID
 */
function getReviewsByUser(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare(
        'SELECT r.*, c.name as canteen_name 
         FROM reviews r 
         JOIN canteens c ON r.canteen_id = c.canteen_id 
         WHERE r.student_id = ? 
         ORDER BY r.created_at DESC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Get complaints by user ID
 */
function getComplaintsByUser(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare(
        'SELECT c.*, canteen.name as canteen_name 
         FROM complaints c 
         JOIN canteens canteen ON c.canteen_id = canteen.canteen_id 
         WHERE c.student_id = ? 
         ORDER BY c.created_at DESC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Update review
 */
function updateReview(PDO $pdo, int $reviewId, int $userId, int $rating, string $comment): bool
{
    $stmt = $pdo->prepare(
        'UPDATE reviews SET rating = ?, comment = ? WHERE review_id = ? AND student_id = ?'
    );
    return $stmt->execute([$rating, $comment, $reviewId, $userId]);
}

/**
 * Delete review
 */
function deleteReview(PDO $pdo, int $reviewId, int $userId): bool
{
    $stmt = $pdo->prepare('DELETE FROM reviews WHERE review_id = ? AND student_id = ?');
    return $stmt->execute([$reviewId, $userId]);
}

/**
 * Get review by ID (ensures user ownership)
 */
function getReviewById(PDO $pdo, int $reviewId, int $userId): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM reviews WHERE review_id = ? AND student_id = ?');
    $stmt->execute([$reviewId, $userId]);
    $review = $stmt->fetch();
    return $review ?: null;
}

/**
 * Get student info by ID for owner dashboard
 */
function getStudentInfo(PDO $pdo, int $studentId): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM students WHERE student_id = ?');
    $stmt->execute([$studentId]);
    $student = $stmt->fetch();
    return $student ?: null;
}
