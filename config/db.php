<?php
$host = "localhost";
$user = "root";
$pass = "Dimas3104";
$db   = "bellisca_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}
?>
