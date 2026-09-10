<?php
try {
    $conn = new PDO('mysql:host=localhost;dbname=mondal-vr', 'root', '');
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    echo "--- categories ---\n";
    print_r($conn->query('SELECT id, name FROM categories')->fetchAll());

    echo "\n--- parent_categories ---\n";
    print_r($conn->query('SELECT id, name FROM parent_categories')->fetchAll());
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
