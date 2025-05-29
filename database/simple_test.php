<?php 
echo "PHP is working\n";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=phichaia_app', 'root', '');
    echo "Database connection successful\n";
    
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables found: " . count($tables) . "\n";
    foreach($tables as $table) {
        echo "- $table\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
