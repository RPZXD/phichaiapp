<?php
require_once __DIR__ . '/../config/Database.php';
use App\Database;

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "=== Database Tables ===\n";
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach($tables as $table) {
        echo "- $table\n";
    }
    
    echo "\n=== Checking specific tables ===\n";
    $requiredTables = ['departments', 'roles', 'user_roles', 'permissions'];
    
    foreach($requiredTables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        $exists = $stmt->rowCount() > 0;
        echo "$table: " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
        
        if($exists && $table === 'departments') {
            echo "  Checking departments data:\n";
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM departments");
            $count = $stmt->fetch()['count'];
            echo "  Department count: $count\n";
        }
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
