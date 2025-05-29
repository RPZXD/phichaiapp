<?php
$pdo = new PDO('mysql:host=localhost;dbname=phichaia_app', 'root', '');
echo "Roles table columns:\n";
$stmt = $pdo->query('DESCRIBE roles');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- " . $row['Field'] . "\n";
}
?>
