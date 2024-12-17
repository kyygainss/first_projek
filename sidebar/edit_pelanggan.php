<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika user bukan admin, arahkan ke halaman login
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Include config.php untuk koneksi ke database
include('../config.php');

// Cek jika user bukan admin, arahkan ke halaman login
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Tangkap nama_penerima yang dikirimkan dari halaman sebelumnya (edit)
$nama_penerima = isset($_GET['nama_penerima']) ? $_GET['nama_penerima'] : '';

// Query untuk mengambil data berdasarkan nama_penerima yang sama
$sql = "SELECT nama_penerima, alamat_pengiriman 
        FROM riwayat
        WHERE nama_penerima = :nama_penerima
        LIMIT 1"; // Batasi hanya untuk menampilkan 1 data di form

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':nama_penerima', $nama_penerima);
$stmt->execute();
$riwayat = $stmt->fetch(PDO::FETCH_ASSOC); // Ambil satu baris data

// Cek apakah data ditemukan
if (!$riwayat) {
    die('Data tidak ditemukan.');
}

// Proses edit data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $nama_penerima_baru = $_POST['nama_penerima'];
    $alamat_pengiriman_baru = $_POST['alamat_pengiriman'];

    // Update semua data dengan nama_penerima yang sama
    $updateSql = "UPDATE riwayat 
                  SET nama_penerima = :nama_penerima_baru, 
                      alamat_pengiriman = :alamat_pengiriman_baru 
                  WHERE nama_penerima = :old_nama_penerima";

    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->bindValue(':nama_penerima_baru', $nama_penerima_baru);
    $updateStmt->bindValue(':alamat_pengiriman_baru', $alamat_pengiriman_baru);
    $updateStmt->bindValue(':old_nama_penerima', $nama_penerima);
    $updateStmt->execute();

    // Redirect setelah edit
    header('Location: pelanggan.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pelanggan</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal flex items-center justify-center min-h-screen">

    <!-- Card Container -->
    <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-lg">
        <h2 class="text-2xl font-bold text-gray-700 mb-6 text-center">Edit Pelanggan</h2>

        <!-- Form Edit Pelanggan -->
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-gray-700 mb-2">Nama Penerima</label>
                <input type="text" name="nama_penerima" value="<?= htmlspecialchars($riwayat['nama_penerima']) ?>" class="border rounded-lg p-3 w-full focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label class="block text-gray-700 mb-2">Alamat Pengiriman</label>
                <input type="text" name="alamat_pengiriman" value="<?= htmlspecialchars($riwayat['alamat_pengiriman']) ?>" class="border rounded-lg p-3 w-full focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="flex justify-between">
                <a href="pelanggan.php" class="bg-red-500 text-white px-4 py-2 rounded-lg flex items-center transition duration-300 ease-in-out hover:bg-red-700 hover:shadow-lg">
                    Kembali
                </a>
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</body>
</html>
