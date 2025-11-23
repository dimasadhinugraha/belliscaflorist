<?php
session_start();
include "config/db.php";
$id = $_GET['id'];
$conn->query("DELETE FROM produk WHERE id=$id");
header("Location: products.php");
exit();
?>
