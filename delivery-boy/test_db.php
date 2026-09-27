<?php
require 'includes/config.php';
$stmt = $conn->query('SHOW COLUMNS FROM delivery_boys');
while($row = $stmt->fetch()) { echo $row['Field'] . ' - ' . $row['Type'] . "\n"; }
