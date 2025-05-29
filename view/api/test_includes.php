<?php
echo "Testing include paths from UserController directory...\n";

try {
    echo "Current directory: " . getcwd() . "\n";
    
    echo "Testing User model...\n";
    require_once('../../models/User.php');
    echo "✅ User.php loaded\n";
    
    echo "Testing Role model...\n";
    require_once('../../models/Role.php');
    echo "✅ Role.php loaded\n";
    
    echo "Testing Department model...\n";
    require_once('../../models/Department.php');
    echo "✅ Department.php loaded\n";
    
    echo "Testing instantiation...\n";
    $dept = new Department();
    echo "✅ Department instantiated\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
