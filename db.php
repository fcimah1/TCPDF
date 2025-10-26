<?php
$dsn = "mysql:host=localhost;port=3307;dbname=admin_meemkwdb;charset=utf8mb4";
$user = "root";
$pass = "";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    die("DB Connection Failed: " . $e->getMessage());
}
