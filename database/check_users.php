<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=phichaia_app', 'root', '');
    echo "Users table structure:\n";
    $stmt = $pdo->query('DESCRIBE users');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . ' - Key: ' . $row['Key'] . "\n";
    }
    
    echo "\nRoles table structure:\n";
    $stmt = $pdo->query('DESCRIBE roles');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . ' - Key: ' . $row['Key'] . "\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
