<?php
$host = 'localhost';
$user = 'root';
$pass = 'apkharya';
$db   = 'smartkelas';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>