<?php
require_once('../../config/Database.php');
require_once('../../models/User.php');

try {
    echo "Testing User model...\n";
    
    // Create database connection
    $db = new App\Database();
    $conn = $db->getConnection();
    echo "✅ Database connection created\n";
    
    // Try to create User model
    $userModel = new User($conn);
    echo "✅ User model created\n";
    
    // Try to get all users
    $users = $userModel->getAllUsers();
    echo "✅ getAllUsers() called successfully\n";
    echo "Users found: " . count($users) . "\n";
    
    if (count($users) > 0) {
        echo "First user: " . json_encode($users[0]) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
