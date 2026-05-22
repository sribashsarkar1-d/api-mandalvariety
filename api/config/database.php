
<?php
$host = 'localhost';
$db = 'u391326945_mandalvariety';
$user = 'u391326945_mandalvr';
$pass = 'Mandal@1234567890';  

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['success'=>false,'message'=>'DB error']));
}
?>
