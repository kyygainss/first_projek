<?php
session_start();  // Mulai sesi
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Cek apakah email ada di database
        $sql = "SELECT * FROM user WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Menyimpan data sesi setelah login berhasil
                $_SESSION['user_id'] = $user['id'];  // Menyimpan ID pengguna
                $_SESSION['user_type'] = $user['type'];  // Menyimpan tipe pengguna
                $_SESSION['email'] = $user['email'];  // Menyimpan email pengguna
                $_SESSION['nama_panjang'] = $user['nama_panjang'];  // Menyimpan nama panjang pengguna

                // Redirect berdasarkan tipe user
                if ($user['type'] === 'admin') {
                    header('Location: homemin.php');  // Admin ke halaman admin
                } else if ($user['type'] === 'user') {
                    header('Location: homeser.php');  // User ke halaman user
                }
                exit;
            } else {
                echo "<script>alert('Password salah!');</script>";
            }
        } else {
            echo "<script>alert('Email tidak ditemukan!');</script>";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
