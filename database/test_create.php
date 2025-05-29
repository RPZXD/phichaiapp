<?php 
try {
    $pdo = new PDO('mysql:host=localhost;dbname=phichaia_app', 'root', '');
    echo "Testing table creation...\n";
    
    // Test creating a simple table
    $sql = "CREATE TABLE IF NOT EXISTS test_roles (
        role_id INT PRIMARY KEY AUTO_INCREMENT,
        role_name VARCHAR(100) NOT NULL
    )";
    
    $pdo->exec($sql);
    echo "Test table created successfully\n";
    
    // Check if it exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'test_roles'");
    if ($stmt->rowCount() > 0) {
        echo "Test table verified\n";
        // Clean up
        $pdo->exec("DROP TABLE test_roles");
        echo "Test table dropped\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
