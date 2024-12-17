<?php
    $host = 'localhost';
    $dbname = 'tokoonline';
    $username = 'root'; // Sesuaikan dengan username database Anda
    $password = '';     // Kosongkan jika tidak ada password (default XAMPP)

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Koneksi ke database gagal: " . $e->getMessage());
    }
?>
