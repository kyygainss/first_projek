<?php
// Include file konfigurasi koneksi
include('../config.php');


// Cek jika user bukan admin, arahkan ke halaman login
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Ambil id produk dari URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Query untuk mengambil data produk berdasarkan id
    $query = "SELECT * FROM produk WHERE id = ?";
    $stmt = $pdo->prepare($query); // Gunakan $pdo di sini
    $stmt->bindParam(1, $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Jika produk ditemukan, ambil data produk
    if ($result) {
        $produk = $result;
    } else {
        echo "Produk tidak ditemukan";
        exit;
    }
} else {
    echo "ID produk tidak ditemukan";
    exit;
}

// Proses update data produk
if (isset($_POST['update_product'])) {
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $deskripsi = $_POST['deskripsi'];
    $kategori = $_POST['kategori'];
    $stok = $_POST['stok'];
    $status = $_POST['status'];
    $berat = $_POST['berat'];
    $panjang = $_POST['panjang'];
    $lebar = $_POST['lebar'];
    $tinggi = $_POST['tinggi'];

    // Cek apakah ada file gambar yang diupload
    if ($_FILES['gambar']['name']) {
        $gambar = $_FILES['gambar']['name'];
        $file_tmp = $_FILES['gambar']['tmp_name'];

        // Pastikan folder 'file' ada
        if (!is_dir('file')) {
            mkdir('file', 0777, true);
        }

        // Tentukan path tujuan file gambar
        $target_path = 'file/' . $gambar;

        // Pindahkan file gambar ke folder 'file'
        if (move_uploaded_file($file_tmp, $target_path)) {
            // Jika gambar berhasil diupload, gunakan gambar baru
            $gambar_baru = $gambar;
        } else {
            // Jika upload gagal, tetap gunakan gambar lama
            $gambar_baru = $produk['gambar'];
        }
    } else {
        // Jika tidak ada gambar yang diupload, gunakan gambar lama
        $gambar_baru = $produk['gambar'];
    }

    // Query untuk mengupdate data produk
    $update_query = "UPDATE produk SET nama = ?, harga = ?, deskripsi = ?, kategori = ?, stok = ?, status = ?, gambar = ?, berat = ?, panjang = ?, lebar = ?, tinggi = ? WHERE id = ?";
    $stmt = $pdo->prepare($update_query);
    $stmt->bindParam(1, $nama, PDO::PARAM_STR);
    $stmt->bindParam(2, $harga, PDO::PARAM_STR);
    $stmt->bindParam(3, $deskripsi, PDO::PARAM_STR);
    $stmt->bindParam(4, $kategori, PDO::PARAM_STR);
    $stmt->bindParam(5, $stok, PDO::PARAM_INT);
    $stmt->bindParam(6, $status, PDO::PARAM_STR);
    $stmt->bindParam(7, $gambar_baru, PDO::PARAM_STR);
    $stmt->bindParam(8, $berat, PDO::PARAM_STR);
    $stmt->bindParam(9, $panjang, PDO::PARAM_STR);
    $stmt->bindParam(10, $lebar, PDO::PARAM_STR);
    $stmt->bindParam(11, $tinggi, PDO::PARAM_STR);
    $stmt->bindParam(12, $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Menampilkan popup konfirmasi setelah update berhasil
        echo '<script>
                window.onload = function() {
                    let popup = document.getElementById("popup");
                    popup.style.display = "block";
                };
              </script>';
    } else {
        echo 'Gagal mengupdate produk';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <!-- Header -->
    <header class="bg-gray-100 text-white p-5 flex justify-between items-center">
        <h1 class="text-2xl text-black font-bold">Edit Produk</h1>
        <a href="kelproduk.php" class="text-black">Kembali ke Daftar Produk</a>
    </header>

    <!-- Form Edit Produk -->
    <div class="p-8">
        <h2 class="text-3xl font-bold text-gray-700 mb-6">Edit Produk</h2>

        <form action="edit.php?id=<?= $produk['id'] ?>" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="nama" class="block text-gray-700">Nama Produk</label>
                <input type="text" name="nama" id="nama" class="w-full p-2 border border-gray-300 rounded" value="<?= htmlspecialchars($produk['nama']) ?>" required>
            </div>
            <div class="mb-4">
                <label for="harga" class="block text-gray-700">Harga Produk</label>
                <input type="number" name="harga" id="harga" class="w-full p-2 border border-gray-300 rounded" value="<?= htmlspecialchars($produk['harga']) ?>" required>
            </div>
            <div class="mb-4">
                <label for="deskripsi" class="block text-gray-700">Deskripsi Produk</label>
                <textarea name="deskripsi" id="deskripsi" class="w-full p-2 border border-gray-300 rounded" required><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
            </div>
            <div class="mb-4">
                <label for="kategori" class="block text-gray-700">Kategori Produk</label>
                <input type="text" name="kategori" id="kategori" class="w-full p-2 border border-gray-300 rounded" value="<?= htmlspecialchars($produk['kategori']) ?>" required>
            </div>
            <div class="mb-4">
                <label for="stok" class="block text-gray-700">Stok Produk</label>
                <input type="number" name="stok" id="stok" class="w-full p-2 border border-gray-300 rounded" value="<?= htmlspecialchars($produk['stok']) ?>" required>
            </div>
            <div class="mb-4">
                <label for="gambar" class="block text-gray-700">Gambar Produk</label>
                <input type="file" name="gambar" id="gambar" class="w-full p-2 border border-gray-300 rounded">
                <?php if ($produk['gambar']): ?>
                    <p class="text-gray-500 mt-2">Gambar saat ini: <img src="file/<?= $produk['gambar'] ?>" alt="Gambar Produk" class="w-32 h-32 object-cover"></p>
                <?php endif; ?>
            </div>
            <div class="mb-4">
                <label for="status" class="block text-gray-700">Status Produk</label>
                <select name="status" id="status" class="w-full p-2 border border-gray-300 rounded">
                    <option value="aktif" <?= $produk['status'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="tidak aktif" <?= $produk['status'] == 'tidak aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="berat" class="block text-gray-700">Berat Produk (kg)</label>
                <input type="number" name="berat" id="berat" class="w-full p-2 border border-gray-300 rounded" value="<?= htmlspecialchars($produk['berat']) ?>" required>
            </div>
            <div class="mb-4">
                <label for="panjang" class="block text-gray-700">Panjang Produk (cm)</label>
                <input type="number" name="panjang" id="panjang" class="w-full p-2 border border-gray-300 rounded" value="<?= htmlspecialchars($produk['panjang']) ?>" required>
            </div>
            <div class="mb-4">
                <label for="lebar" class="block text-gray-700">Lebar Produk (cm)</label>
                <input type="number" name="lebar" id="lebar" class="w-full p-2 border border-gray-300 rounded" value="<?= htmlspecialchars($produk['lebar']) ?>" required>
            </div>
            <div class="mb-4">
                <label for="tinggi" class="block text-gray-700">Tinggi Produk (cm)</label>
                <input type="number" name="tinggi" id="tinggi" class="w-full p-2 border border-gray-300 rounded" value="<?= htmlspecialchars($produk['tinggi']) ?>" required>
            </div>
            <div class="mb-4">
                <button type="submit" name="update_product" class="bg-blue-500 text-white px-6 py-2 rounded">Update Produk</button>
            </div>
        </form>
    </div>

    <!-- Popup Konfirmasi -->
    <div id="popup" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex justify-center items-center z-50 hidden">
    <div class="bg-white p-6 rounded shadow-lg text-center relative z-60">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Produk Berhasil Diperbarui</h3>
            <p class="mb-6">Apakah Anda ingin kembali ke daftar produk?</p>
            <a href="kelproduk.php" class="bg-green-500 text-white px-4 py-2 rounded mr-2">Iya</a>
            <button onclick="document.getElementById('popup').style.display = 'none'" class="bg-gray-500 text-white px-4 py-2 rounded">Tidak</button>
        </div>
    </div>

    <script>
    // Menampilkan popup
    function showPopup() {
        document.getElementById('popup').classList.remove('hidden');
    }

    // Menyembunyikan popup
    function hidePopup() {
        document.getElementById('popup').style.display = 'none';
    }
    </script>
</body>
</html>
