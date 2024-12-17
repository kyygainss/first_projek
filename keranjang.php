<?php
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['email'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='index.php';</script>";
    exit;
}

// Ambil email dari sesi
$email = $_SESSION['email'];

// Koneksi ke database
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Koneksi gagal: " . $e->getMessage();
}

// Ambil data user_id dari tabel user berdasarkan email
$queryUser = "SELECT id FROM user WHERE email = :email";
$stmtUser = $pdo->prepare($queryUser);
$stmtUser->bindParam(':email', $email, PDO::PARAM_STR);
$stmtUser->execute();
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Cek apakah user ditemukan
if (!$user) {
    echo "<script>alert('User tidak ditemukan.'); window.location.href='index.php';</script>";
    exit;
}

$user_id = $user['id'];

// Query untuk mengambil semua data keranjang berdasarkan email
$query = "SELECT * FROM keranjang WHERE email = :email";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses Checkout
if (isset($_POST['checkout'])) {
    $nama_penerima = $_POST['nama_penerima'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $alamat_pengiriman = $_POST['alamat_pengiriman'];
    $nomor_telepon = $_POST['nomor_telepon'];
    $catatan = $_POST['catatan']; // Menambahkan form catatan

    // Menghitung total harga dari keranjang
    $total_harga = 0;
    foreach ($cartItems as $item) {
        $total_harga += $item['total'];
    }

    // Memasukkan data pesanan ke tabel pesanan
    $queryPesanan = "INSERT INTO pesanan (user_id, nama_produk, harga, qty, total_harga, alamat_pengiriman, kondisi, tanggal_pesanan, metode_pembayaran, nama_penerima, nomor_telepon, catatan) 
                     SELECT :user_id, nama_produk, harga, qty, :total_harga, :alamat_pengiriman, 'Menunggu', NOW(), :metode_pembayaran, :nama_penerima, :nomor_telepon, :catatan 
                     FROM keranjang WHERE email = :email";
    $stmtPesanan = $pdo->prepare($queryPesanan);
    $stmtPesanan->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmtPesanan->bindParam(':total_harga', $total_harga, PDO::PARAM_INT);
    $stmtPesanan->bindParam(':alamat_pengiriman', $alamat_pengiriman, PDO::PARAM_STR);
    $stmtPesanan->bindParam(':metode_pembayaran', $metode_pembayaran, PDO::PARAM_STR);
    $stmtPesanan->bindParam(':nama_penerima', $nama_penerima, PDO::PARAM_STR);
    $stmtPesanan->bindParam(':nomor_telepon', $nomor_telepon, PDO::PARAM_STR);
    $stmtPesanan->bindParam(':catatan', $catatan, PDO::PARAM_STR);
    $stmtPesanan->bindParam(':email', $email, PDO::PARAM_STR);

    if ($stmtPesanan->execute()) {
        // Jika checkout sukses, hapus item dari keranjang
        $queryDeleteCart = "DELETE FROM keranjang WHERE email = :email";
        $stmtDeleteCart = $pdo->prepare($queryDeleteCart);
        $stmtDeleteCart->bindParam(':email', $email, PDO::PARAM_STR);
        $stmtDeleteCart->execute();

        echo "<script>alert('Checkout berhasil!'); window.location.href='keranjang.php';</script>";
    } else {
        echo "<script>alert('Checkout gagal!');</script>";
    }
}

// Proses Hapus Data dari Keranjang
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Query untuk menghapus item berdasarkan ID
    $queryDeleteItem = "DELETE FROM keranjang WHERE id = :id";
    $stmtDeleteItem = $pdo->prepare($queryDeleteItem);
    $stmtDeleteItem->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmtDeleteItem->execute()) {
        echo "<script>alert('Item berhasil dihapus dari keranjang!'); window.location.href='keranjang.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus item!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <!-- Header -->
    <header class="bg-gray-100 text-white p-5 flex justify-between items-center">
        <a href="homeser.php" class="bg-yellow-500 text-white px-4 py-2 rounded-lg flex items-center transition duration-300 ease-in-out hover:bg-gray-300 hover:shadow-lg">Kembali</a>
    </header>

    <!-- Keranjang -->
    <div class="p-8">
        <h2 class="text-3xl font-bold text-gray-700 mb-6">Keranjang Belanja</h2>

        <!-- Kotak-Kotak Produk -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 overflow-y-auto max-h-screen">
            <?php if (count($cartItems) > 0): ?>
                <?php foreach ($cartItems as $item): ?>
                    <?php
                    // Query untuk mengambil data gambar dari produk berdasarkan nama produk
                    $productQuery = "SELECT gambar FROM produk WHERE nama = :nama_produk";
                    $productStmt = $pdo->prepare($productQuery);
                    $productStmt->bindParam(':nama_produk', $item['nama_produk'], PDO::PARAM_STR);
                    $productStmt->execute();
                    $product = $productStmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="bg-white shadow-md rounded-lg p-4">
                        <img src="sidebar/file/<?= htmlspecialchars($product['gambar']) ?>" alt="<?= htmlspecialchars($item['nama_produk']) ?>" class="mb-4 w-full object-cover rounded-md">
                        <h4 class="text-lg font-bold text-gray-700"><?= htmlspecialchars($item['nama_produk']) ?></h4>
                        <p class="text-gray-600 mb-4">Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
                        <p class="text-gray-600 mb-4">Jumlah: <?= htmlspecialchars($item['qty']) ?></p>
                        <p class="text-gray-600 mb-4">Total: Rp <?= number_format($item['total'], 0, ',', '.') ?></p>
                        <div class="flex items-center justify-between mt-4">
                            <a href="?id=<?= $item['id'] ?>" 
                               class="bg-red-500 text-white px-4 py-2 rounded-lg flex items-center transition duration-300 ease-in-out hover:bg-red-400">
                               Hapus
                            </a>
                            <a href="checkout.php?id=<?php echo $item['id']; ?>" 
                               class="bg-green-500 text-white px-4 py-2 rounded-lg flex items-center transition duration-300 ease-in-out hover:bg-green-400">
                               Checkout
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500 col-span-4 text-center">Keranjang Anda kosong.</p>
            <?php endif; ?>
        </div>

        <!-- Total Belanja -->
        <div class="mt-6">
            <?php
            $totalBelanja = 0;
            foreach ($cartItems as $item) {
                $totalBelanja += $item['total'];
            }
            ?>
            <p class="text-lg font-bold">Total Belanja: Rp <?= number_format($totalBelanja, 0, ',', '.') ?></p>
        </div>

        <!-- Tombol Checkout Semua -->
        <div class="mt-4">
            <button onclick="openModal()" class="bg-blue-500 text-white px-6 py-2 rounded-lg">Checkout Semua</button>
        </div>
    </div>

    <!-- Modal Checkout -->
    <div id="checkoutModal" class="fixed inset-0 bg-gray-500 flex justify-center items-center hidden">
        <div class="bg-white p-8 rounded-lg w-96">
            <h3 class="text-2xl font-bold text-gray-700 mb-4">Formulir Checkout</h3>
            <form action="" method="POST">
                <div class="mb-4">
                    <label for="nama_penerima" class="block text-sm text-gray-700">Nama Penerima</label>
                    <input type="text" id="nama_penerima" name="nama_penerima" class="w-full p-2 border border-gray-300 rounded" required>
                </div>
                <div class="mb-4">
                    <label for="metode_pembayaran" class="block text-sm text-gray-700">Metode Pembayaran</label>
                    <select id="metode_pembayaran" name="metode_pembayaran" class="w-full p-2 border border-gray-300 rounded" required>
                        <option value="">Pilih Metode Pembayaran</option> <!-- Pilihan default kosong -->
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="COD">COD</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="alamat_pengiriman" class="block text-sm text-gray-700">Alamat Pengiriman</label>
                    <textarea id="alamat_pengiriman" name="alamat_pengiriman" class="w-full p-2 border border-gray-300 rounded" required></textarea>
                </div>
                <div class="mb-4">
                    <label for="nomor_telepon" class="block text-sm text-gray-700">Nomor Telepon</label>
                    <input type="text" id="nomor_telepon" name="nomor_telepon" class="w-full p-2 border border-gray-300 rounded" required>
                </div>
                <div class="mb-4">
                    <label for="catatan" class="block text-sm text-gray-700">Catatan</label>
                    <textarea id="catatan" name="catatan" class="w-full p-2 border border-gray-300 rounded"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" name="checkout" class="bg-blue-500 text-white px-6 py-2 rounded-lg">Checkout</button>
                    <button type="button" onclick="closeModal()" class="ml-4 bg-gray-500 text-white px-6 py-2 rounded-lg">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('checkoutModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('checkoutModal').style.display = 'none';
        }

        // Validasi form sebelum submit
        document.querySelector('form').onsubmit = function(event) {
            const metodePembayaran = document.getElementById('metode_pembayaran').value;
            if (!metodePembayaran) {
                alert('Metode pembayaran tidak boleh kosong!');  // Menampilkan pesan error jika tidak dipilih
                event.preventDefault();  // Mencegah form dikirim
            }
        }
    </script>
</body>
</html>
