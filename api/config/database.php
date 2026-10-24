
<?php
$host = 'localhost';
$db = 'u391326945_mandal';
$user = 'u391326945_mandal';
$pass = 'Sribash123';  

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=mondal-vr;charset=utf8mb4", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e2) {
        die(json_encode(['success'=>false,'message'=>'DB error']));
    }
}

require_once __DIR__ . '/category_helper.php';
?>
