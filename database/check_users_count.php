<?php
$pdo = new PDO('mysql:host=localhost;dbname=phichaia_app', 'root', '');
echo "Checking users table...\n";
$stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
$count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Total users: $count\n";

if ($count > 0) {
    echo "\nFirst 5 users:\n";
    $stmt = $pdo->query('SELECT user_id, username, email, role FROM users LIMIT 5');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- ID: {$row['user_id']}, Username: {$row['username']}, Email: {$row['email']}, Role: {$row['role']}\n";
    }
}
?>
