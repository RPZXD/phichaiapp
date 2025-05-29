<?php
echo "Testing UserController step by step...\n";

// Test Database connection first
echo "1. Testing Database connection...\n";
try {
    require_once('../../config/Database.php');
    $db = new App\Database();
    $conn = $db->getConnection();
    echo "✅ Database connection successful\n";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit;
}

// Test individual model loading
echo "2. Testing model loading...\n";
try {
    require_once('../../models/User.php');
    echo "✅ User model loaded\n";
    
    require_once('../../models/Role.php');
    echo "✅ Role model loaded\n";
    
    require_once('../../models/Department.php');
    echo "✅ Department model loaded\n";
    
} catch (Exception $e) {
    echo "❌ Model loading error: " . $e->getMessage() . "\n";
    exit;
}

// Test model instantiation
echo "3. Testing model instantiation...\n";
try {
    $roleModel = new Role($conn);
    echo "✅ Role model instantiated\n";
    
    $departmentModel = new Department($conn);
    echo "✅ Department model instantiated\n";
    
} catch (Exception $e) {
    echo "❌ Model instantiation error: " . $e->getMessage() . "\n";
}

echo "All tests completed!\n";
?>
