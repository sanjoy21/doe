<?php
// Database connection
$host = 'localhost';
$db   = 'emergencyskills_doe';
$user = 'emergencyskills_user';
$pass = 'G4DXwsx5TzyDgU6';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// 1. Fetch all users who haven't been hashed yet
$stmt = $pdo->query("SELECT id, password FROM user WHERE password_hash IS NULL OR password_hash = ''");
$users = $stmt->fetchAll();

echo "Starting migration for " . count($users) . " users...\n";

// 2. Prepare the update statement
$updateStmt = $pdo->prepare("UPDATE user SET password_hash = ? WHERE id = ?");

foreach ($users as $user) {
    // Hash the plaintext password using standard BCrypt
    $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
    
    // Update the row
    $updateStmt->execute([$hashedPassword, $user['id']]);
}

echo "Migration complete!";
?>