<?php
echo "Testing Department class loading...\n";

try {
    require_once('../../models/Department.php');
    echo "✅ Department.php loaded successfully\n";
    
    $dept = new Department();
    echo "✅ Department class instantiated successfully\n";
    
    $departments = $dept->getAllDepartments();
    echo "✅ getAllDepartments() called successfully\n";
    echo "Departments found: " . count($departments) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
