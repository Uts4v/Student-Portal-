<?php
//database config
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_portal";
$charset = "utf8mb4";

$dsn = "mysql:host=$servername;dbname=$dbname;charset=$charset";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (!isset($pdo) || !$pdo) {
    die("PDO connection was not established.");
}

session_start();
?>