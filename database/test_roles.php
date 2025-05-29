<?php 
try {
    $pdo = new PDO('mysql:host=localhost;dbname=phichaia_app', 'root', '');
    echo "Testing roles table creation...\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS `roles` (
        `role_id` INT PRIMARY KEY AUTO_INCREMENT,
        `role_code` VARCHAR(50) UNIQUE NOT NULL,
        `role_name` VARCHAR(100) NOT NULL,
        `role_description` TEXT,
        `role_level` INT DEFAULT 1,
        `is_active` BOOLEAN DEFAULT TRUE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_role_code` (`role_code`),
        INDEX `idx_role_level` (`role_level`)
    )";
    
    $pdo->exec($sql);
    echo "Roles table created successfully\n";
    
    // Check if it exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'roles'");
    if ($stmt->rowCount() > 0) {
        echo "Roles table verified\n";
    } else {
        echo "Roles table NOT found\n";
    }
    
    // Check all tables now
    echo "\nAll tables:\n";
    $stmt = $pdo->query("SHOW TABLES");
    foreach($stmt->fetchAll(PDO::FETCH_COLUMN) as $table) {
        echo "- $table\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
