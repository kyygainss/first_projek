<?php
session_start();

// Pastikan user sudah login dan tipe user adalah 'user'
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'user') {
    header("Location: index.php");
    exit;
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

require_once 'config.php';

// Ambil data kategori dari database
$queryKategori = "SELECT DISTINCT kategori FROM produk";
$stmtKategori = $pdo->prepare($queryKategori);
$stmtKategori->execute();
$categories = $stmtKategori->fetchAll(PDO::FETCH_ASSOC);

// Ambil data produk berdasarkan kategori jika ada query parameter kategori atau pencarian
$categoryFilter = isset($_GET['kategori']) ? $_GET['kategori'] : null;
$searchQuery = isset($_GET['search']) ? $_GET['search'] : null;

if ($categoryFilter) {
    $queryProduk = "SELECT * FROM produk WHERE kategori = :kategori";
    $stmtProduk = $pdo->prepare($queryProduk);
    $stmtProduk->bindParam(':kategori', $categoryFilter, PDO::PARAM_STR);
} elseif ($searchQuery) {
    $queryProduk = "SELECT * FROM produk WHERE nama LIKE :search";
    $stmtProduk = $pdo->prepare($queryProduk);
    $stmtProduk->bindValue(':search', '%' . $searchQuery . '%', PDO::PARAM_STR);
} else {
    $queryProduk = "SELECT * FROM produk";
    $stmtProduk = $pdo->prepare($queryProduk);
}

$stmtProduk->execute();
$products = $stmtProduk->fetchAll(PDO::FETCH_ASSOC);

// Ambil email user yang sedang login dari session
$userEmail = $_SESSION['user_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $productId = $_GET['id'];

    // Ambil detail produk berdasarkan ID
    $queryProduct = "SELECT * FROM produk WHERE id = :id";
    $stmtProduct = $pdo->prepare($queryProduct);
    $stmtProduct->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmtProduct->execute();
    $product = $stmtProduct->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "<script>alert('Produk tidak ditemukan!'); window.location.href='index.php';</script>";
        exit;
    }
}

// Proses penyimpanan pesanan jika form dikirimkan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $namaPenerima = $_POST['nama_penerima'];
    $nomorTelepon = $_POST['nomor_telepon'];
    $alamatPengiriman = $_POST['alamat_pengiriman'];

    // Validasi input
    if ($productId && $quantity > 0 && $namaPenerima && $nomorTelepon && $alamatPengiriman) {
        // Ambil detail produk
        $queryProduct = "SELECT * FROM produk WHERE id = :email";
        $stmtProduct = $pdo->prepare($queryProduct);
        $stmtProduct->bindParam(':email', $productId, PDO::PARAM_INT);
        $stmtProduct->execute();
        $product = $stmtProduct->fetch(PDO::FETCH_ASSOC);

        if ($product && $product['stok'] >= $quantity) {
            $totalHarga = $product['harga'] * $quantity;

            // Simpan pesanan
            $queryInsert = "INSERT INTO pesanan (user_id, produk_id, nama_produk, harga, qty, total_harga, alamat_pengiriman, kondisi, metode_pembayaran, nama_penerima, nomor_telepon, catatan)
            VALUES (:user_id, :produk_id, :nama_produk, :harga, :qty, :total_harga, :alamat_pengiriman, 'Menunggu', 'Transfer', :nama_penerima, :nomor_telepon, :catatan)";
            $stmtInsert = $pdo->prepare($queryInsert);
            $stmtInsert->execute([
                ':user_id' => $_SESSION['user_id'],
                ':produk_id' => $productId,
                ':nama_produk' => $product['nama'],
                ':harga' => $product['harga'],
                ':qty' => $quantity,
                ':total_harga' => $totalHarga,
                ':alamat_pengiriman' => $alamatPengiriman,
                ':nama_penerima' => $namaPenerima,
                ':nomor_telepon' => $nomorTelepon,
                ':catatan' => $_POST['catatan'], // Tambahkan catatan jika ada
            ]);

            // Update stok produk
            $newStock = $product['stok'] - $quantity;
            $queryUpdateStock = "UPDATE produk SET stok = :stok WHERE id = :id";
            $stmtUpdateStock = $pdo->prepare($queryUpdateStock);
            $stmtUpdateStock->bindParam(':stok', $newStock, PDO::PARAM_INT);
            $stmtUpdateStock->bindParam(':id', $productId, PDO::PARAM_INT);
            $stmtUpdateStock->execute();

            echo "<script>alert('Pesanan berhasil dibuat!'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Stok tidak mencukupi!');</script>";
        }
    } else {
        echo "<script>alert('Mohon lengkapi semua data!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beli Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
<header class="bg-gray-100 text-white p-5 flex justify-between items-center">
    <a href="homeser.php" class="bg-yellow-500 text-white px-4 py-2 rounded-lg flex items-center transition duration-300 ease-in-out hover:bg-gray-300 hover:shadow-lg">Kembali</a>
</header>
<div class="container mx-auto mt-8 px-4">
    <form method="POST" class="bg-white p-6 rounded-lg shadow-lg max-w-lg mx-auto">
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
        <div class="mb-4 flex justify-center items-center">
            <img src="sidebar/file/<?= htmlspecialchars($product['gambar']) ?>" alt="<?= htmlspecialchars($product['nama']) ?>" class="w-[150px] h-[150px] object-cover">
        </div>
        <p class="text-lg font-bold mb-2 text-center"><?= htmlspecialchars($product['nama']) ?></p>
        <p class="text-black-700 mb-2 text-center">Harga: Rp <?= number_format($product['harga'], 0, ',', '.') ?></p>
        
        <div class="mb-4">
            <label for="quantity" class="block text-gray-700">Jumlah:</label>
            <input type="number" name="quantity" id="quantity" class="border border-gray-300 rounded p-2 w-full" required>
        </div>
        
        <div class="mb-4">
            <label for="total_price" class="block text-gray-700">Total Harga:</label>
            <input type="text" id="total_price" class="border border-gray-300 rounded p-2 w-full bg-gray-100" readonly>
        </div>
        
        <div class="mb-4">
            <label for="nama_penerima" class="block text-gray-700">Nama Penerima:</label>
            <input type="text" name="nama_penerima" id="nama_penerima" class="border border-gray-300 rounded p-2 w-full" required>
        </div>
        
        <div class="mb-4">
            <label for="alamat_pengiriman" class="block text-gray-700">Alamat Pengiriman:</label>
            <textarea name="alamat_pengiriman" id="alamat_pengiriman" class="border border-gray-300 rounded p-2 w-full" required></textarea>
        </div>
        
        <div class="mb-4">
            <label for="nomor_telepon" class="block text-gray-700">Nomor Telepon:</label>
            <input type="text" name="nomor_telepon" id="nomor_telepon" class="border border-gray-300 rounded p-2 w-full" required>
        </div>
        
        <div class="mb-4">
            <label for="catatan" class="block text-gray-700">Catatan untuk Seller:</label>
            <textarea name="catatan" id="catatan" class="border border-gray-300 rounded p-2 w-full" placeholder="Masukkan catatan untuk penjual jika ada..."></textarea>
        </div>
        
        <button type="submit" class="bg-blue-500 text-white px-6 py-3 rounded-lg w-full">Pesan Sekarang</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const quantityInput = document.getElementById('quantity');
        const totalPriceInput = document.getElementById('total_price');
        const productPrice = <?= $product['harga'] ?>; // Harga produk dari PHP

        // Update total harga saat quantity berubah
        quantityInput.addEventListener('input', () => {
            const quantity = parseInt(quantityInput.value) || 0;
            const totalPrice = quantity * productPrice;
            totalPriceInput.value = 'Rp ' + totalPrice.toLocaleString();
        });
    });
</script>
</body>
</html>
