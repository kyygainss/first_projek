<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Periksa apakah sesi 'user_type' sudah diset, dan pastikan tipe pengguna adalah 'admin'
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");  // Arahkan ke halaman login jika pengguna bukan admin
    exit;
}

// Koneksi ke database (ganti dengan informasi koneksi Anda)
include '../config.php';

// Validasi dan tambah produk
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $errors = [];

    // Ambil data dari form
    $nama = trim($_POST['nama']);
    $harga = $_POST['harga'];
    $deskripsi = trim($_POST['deskripsi']);
    $kategori = trim($_POST['kategori']);
    $stok = $_POST['stok'];
    $gambar = $_FILES['gambar']['name'];
    $status = $_POST['status'];
    $berat = $_POST['berat'];
    $panjang = $_POST['panjang'];
    $lebar = $_POST['lebar'];
    $tinggi = $_POST['tinggi'];

    // Validasi input
    if (empty($nama)) {
        $errors[] = "Nama produk tidak boleh kosong.";
    }
    if (empty($harga) || !is_numeric($harga) || $harga <= 0) {
        $errors[] = "Harga produk harus diisi dan berupa angka positif.";
    }
    if (empty($deskripsi)) {
        $errors[] = "Deskripsi produk tidak boleh kosong.";
    }
    if (empty($kategori)) {
        $errors[] = "Kategori produk tidak boleh kosong.";
    }
    if (empty($stok) || !is_numeric($stok) || $stok < 0) {
        $errors[] = "Stok produk harus diisi dan berupa angka positif.";
    }
    if (empty($berat) || !is_numeric($berat) || $berat <= 0) {
        $errors[] = "Berat produk harus diisi dan berupa angka positif.";
    }
    if (empty($panjang) || !is_numeric($panjang) || $panjang <= 0) {
        $errors[] = "Panjang produk harus diisi dan berupa angka positif.";
    }
    if (empty($lebar) || !is_numeric($lebar) || $lebar <= 0) {
        $errors[] = "Lebar produk harus diisi dan berupa angka positif.";
    }
    if (empty($tinggi) || !is_numeric($tinggi) || $tinggi <= 0) {
        $errors[] = "Tinggi produk harus diisi dan berupa angka positif.";
    }

    // Jika ada error, tampilkan pesan error
    if (empty($errors)) {
        // Upload gambar produk jika ada
        if ($gambar) {
            $target_dir = "../sidebar/file/"; // Set folder tujuan gambar
            $target_file = $target_dir . basename($gambar); // Tentukan path lengkap untuk file
            // Memindahkan gambar ke folder tujuan
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                // Jika gambar berhasil di-upload
                echo "Gambar berhasil di-upload.";
            } else {
                $errors[] = "Gagal meng-upload gambar.";
            }
        }

        // Query untuk menambah produk
        $sql = "INSERT INTO produk (nama, harga, deskripsi, kategori, stok, gambar, status, berat, panjang, lebar, tinggi)
                VALUES (:nama, :harga, :deskripsi, :kategori, :stok, :gambar, :status, :berat, :panjang, :lebar, :tinggi)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nama' => $nama,
            ':harga' => $harga,
            ':deskripsi' => $deskripsi,
            ':kategori' => $kategori,
            ':stok' => $stok,
            ':gambar' => $gambar,
            ':status' => $status,
            ':berat' => $berat,
            ':panjang' => $panjang,
            ':lebar' => $lebar,
            ':tinggi' => $tinggi
        ]);

        echo "<script>alert('Produk berhasil ditambahkan!'); window.location='kelproduk.php';</script>";
    }
}

// Mengambil semua produk dari database untuk ditampilkan
$sql = "SELECT * FROM produk";
$stmt = $pdo->query($sql);
$produk = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <!-- Header -->
    <header class="bg-gray-100 text-white p-5 flex justify-between items-center">
        <div class="text-black"><?php include('../templatemenu.php'); ?></div>
        <!-- <a href="../homemin.php" class="text-black">Kembali ke Dashboard</a> -->
    </header>

    <!-- Form Tambah Produk -->
    <div class="p-8">
        <h2 class="text-3xl font-bold text-gray-700 mb-6">Tambah Produk</h2>
        
        <!-- Tampilkan pesan error jika ada -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-500 text-white p-4 rounded mb-6">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="kelproduk.php" method="POST" enctype="multipart/form-data">
            <!-- Input Fields -->
            <div class="mb-4">
                <label for="nama" class="block text-gray-700">Nama Produk</label>
                <input type="text" name="nama" id="nama" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="harga" class="block text-gray-700">Harga Produk</label>
                <input type="number" name="harga" id="harga" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="deskripsi" class="block text-gray-700">Deskripsi Produk</label>
                <textarea name="deskripsi" id="deskripsi" class="w-full p-2 border border-gray-300 rounded" required></textarea>
            </div>
            <div class="mb-4">
                <label for="kategori" class="block text-gray-700">Kategori Produk</label>
                <input type="text" name="kategori" id="kategori" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="stok" class="block text-gray-700">Stok Produk</label>
                <input type="number" name="stok" id="stok" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="gambar" class="block text-gray-700">Gambar Produk</label>
                <input type="file" name="gambar" id="gambar" class="w-full p-2 border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="status" class="block text-gray-700">Status Produk</label>
                <select name="status" id="status" class="w-full p-2 border border-gray-300 rounded">
                    <option value="aktif">Aktif</option>
                    <option value="tidak aktif">Tidak Aktif</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="berat" class="block text-gray-700">Berat Produk (kg)</label>
                <input type="number" step="0.01" name="berat" id="berat" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="panjang" class="block text-gray-700">Panjang Produk (cm)</label>
                <input type="number" step="0.01" name="panjang" id="panjang" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="lebar" class="block text-gray-700">Lebar Produk (cm)</label>
                <input type="number" step="0.01" name="lebar" id="lebar" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="tinggi" class="block text-gray-700">Tinggi Produk (cm)</label>
                <input type="number" step="0.01" name="tinggi" id="tinggi" class="w-full p-2 border border-gray-300 rounded" required>
            </div>

            <button type="submit" name="add_product" class="bg-blue-500 text-white p-2 rounded">Tambah Produk</button>
        </form>
    </div>

    <!-- Menampilkan Produk -->
    <div class="p-8">
        <h2 class="text-3xl font-bold text-gray-700 mb-6">Daftar Produk</h2>
        <table class="w-full table-auto border-collapse border border-gray-300">
            <thead>
                <tr>
                    <!-- Kolom Nama dan Aksi untuk Mobile -->
                    <th class="border border-gray-300 px-4 py-2 text-left md:hidden">Nama</th>
                    <th class="border border-gray-300 px-4 py-2 text-left md:hidden">Aksi</th>

                    <!-- Kolom Lengkap untuk Desktop -->
                    <th class="border border-gray-300 px-4 py-2 text-left hidden md:table-cell">Nama</th>
                    <th class="border border-gray-300 px-4 py-2 text-left hidden md:table-cell">Harga</th>
                    <th class="border border-gray-300 px-4 py-2 text-left hidden md:table-cell">Deskripsi</th>
                    <th class="border border-gray-300 px-4 py-2 text-left hidden md:table-cell">Status</th>
                    <th class="border border-gray-300 px-4 py-2 text-left hidden md:table-cell">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produk as $p): ?>
                <tr>
                    <!-- Tampilkan Nama dan Aksi untuk Mobile -->
                    <td class="border border-gray-300 px-4 py-2 md:hidden"><?= htmlspecialchars($p['nama']) ?></td>
                    <td class="border border-gray-300 px-4 py-2 md:hidden">
                        <a href="edit.php?id=<?= $p['id'] ?>" class="bg-yellow-500 text-white py-1 px-3 rounded">Edit</a>
                    </td>

                    <!-- Tampilkan Semua Kolom untuk Desktop -->
                    <td class="border border-gray-300 px-4 py-2 hidden md:table-cell"><?= htmlspecialchars($p['nama']) ?></td>
                    <td class="border border-gray-300 px-4 py-2 hidden md:table-cell"><?= htmlspecialchars($p['harga']) ?></td>
                    <td class="border border-gray-300 px-4 py-2 hidden md:table-cell"><?= htmlspecialchars($p['deskripsi']) ?></td>
                    <td class="border border-gray-300 px-4 py-2 hidden md:table-cell"><?= htmlspecialchars($p['status']) ?></td>
                    <td class="border border-gray-300 px-4 py-2 hidden md:table-cell">
                        <a href="edit.php?id=<?= $p['id'] ?>" class="bg-yellow-500 text-white py-1 px-3 rounded">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
