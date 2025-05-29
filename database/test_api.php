<?php
echo "Testing API endpoints...\n\n";

// Test departments endpoint
echo "1. Testing departments endpoint:\n";
$response = file_get_contents('http://localhost/phichaiapp/view/api/UserController.php?action=getDepartments');
echo "Response: " . $response . "\n\n";

// Test roles endpoint
echo "2. Testing roles endpoint:\n";
$response = file_get_contents('http://localhost/phichaiapp/view/api/UserController.php?action=getRoles');
echo "Response: " . $response . "\n\n";

// Test users endpoint
echo "3. Testing users endpoint:\n";
$response = file_get_contents('http://localhost/phichaiapp/view/api/UserController.php?action=getAllUsers');
echo "Response: " . $response . "\n\n";

echo "API test completed!\n";
?>
