<?php
try {
    $conn = new PDO('mysql:host=localhost;dbname=mondal-vr', 'root', '');
    print_r($conn->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));
} catch(Exception $e) {
    echo $e->getMessage();
}
