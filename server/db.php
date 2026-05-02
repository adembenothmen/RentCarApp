<?php


$user = "root";
$pass = "";

try {
    $db = new PDO('mysql:host=localhost;dbname=rentcar;port=3306;charset=utf8', $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    echo("Connection failed: " . $e->getMessage());
}


?>
