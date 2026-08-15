
<?php
$host = 'localhost';
$db = 'u391326945_mandal';
$user = 'u391326945_mandal';
$pass = 'Sribash123';  

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['success'=>false,'message'=>'DB error']));
}
?>
