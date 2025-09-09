<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "tnvs";

try {
    $dbh = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection error: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
?>