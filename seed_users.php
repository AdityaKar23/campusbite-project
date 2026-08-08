<?php
/**
 * One-time script to create demo user accounts.
 * Visit once after importing campusbite.sql, then delete this file.
 */
require_once 'db.php';

$pdo = getDB();

$count = (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn() + (int) $pdo->query('SELECT COUNT(*) FROM canteen_owners')->fetchColumn();

if ($count > 0) {
    die('Users already exist. Delete seed_users.php for security.');
}

$demoPassword = password_hash('password123', PASSWORD_DEFAULT);

// Insert students
$studentStmt = $pdo->prepare(
    'INSERT INTO students (university_id, password, full_name, department) VALUES (?, ?, ?, ?)'
);

$studentStmt->execute(['2430901', $demoPassword, 'Student 1', 'CSE']);
$studentStmt->execute(['2430109', $demoPassword, 'Student 2', 'CSE']);
$studentStmt->execute(['2340888', $demoPassword, 'Student 3', 'EEE']);
$studentStmt->execute(['2430876', $demoPassword, 'Student 4', 'BBA']);

// Insert canteen owners
$ownerStmt = $pdo->prepare(
    'INSERT INTO canteen_owners (university_id, password, full_name, canteen_id) VALUES (?, ?, ?, ?)'
);

$ownerStmt->execute(['4006', $demoPassword, 'Owner 1', 1]);
$ownerStmt->execute(['5678', $demoPassword, 'Owner 2', 2]);
$ownerStmt->execute(['7865', $demoPassword, 'Owner 3', 4]);

echo '<div style="font-family:sans-serif;padding:2rem;">';
echo '<h2>Demo users created successfully!</h2>';
echo '<h3>Students:</h3>';
echo '<p><strong>Student 1:</strong> University ID <code>2430901</code>, Password <code>password123</code></p>';
echo '<p><strong>Student 2:</strong> University ID <code>2430109</code>, Password <code>password123</code></p>';
echo '<p><strong>Student 3:</strong> University ID <code>2340888</code>, Password <code>password123</code></p>';
echo '<p><strong>Student 4:</strong> University ID <code>2430876</code>, Password <code>password123</code></p>';
echo '<h3>Restaurant Owners:</h3>';
echo '<p><strong>Owner 1:</strong> University ID <code>4006</code>, Password <code>password123</code></p>';
echo '<p><strong>Owner 2:</strong> University ID <code>5678</code>, Password <code>password123</code></p>';
echo '<p><strong>Owner 3:</strong> University ID <code>7865</code>, Password <code>password123</code></p>';
echo '<p><a href="login.php">Go to Login</a></p>';
echo '<p style="color:#c0392b;"><strong>Delete seed_users.php after use.</strong></p>';
echo '</div>';
