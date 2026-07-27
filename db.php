<?php
/**
 * CampusBite - Database Connection
 * Uses PDO with prepared statements for secure queries
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'campusbite');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Returns a PDO database connection
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('<div class="alert alert-danger m-4">Database connection failed. Please import campusbite.sql and ensure XAMPP MySQL is running.</div>');
        }
    }

    return $pdo;
}

/**
 * Fetch a single canteen by ID
 */
function getCanteen(PDO $pdo, int $canteenId): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM canteens WHERE canteen_id = ?');
    $stmt->execute([$canteenId]);
    $canteen = $stmt->fetch();

    return $canteen ?: null;
}

/**
 * Render star icons for a given rating (1-5)
 */
function renderStars(int $rating): string
{
    $html = '<span class="star-rating" aria-label="' . $rating . ' out of 5 stars">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $rating
            ? '<i class="bi bi-star-fill text-warning"></i>'
            : '<i class="bi bi-star text-muted"></i>';
    }
    $html .= '</span>';

    return $html;
}
