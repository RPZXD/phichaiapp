<?php
$pdo = new PDO('mysql:host=localhost;dbname=phichaia_app', 'root', '');
echo "Departments table columns:\n";
$stmt = $pdo->query('DESCRIBE departments');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- " . $row['Field'] . "\n";
}
?>
