<?php
session_start();

// Pastikan user sudah login dan tipe user adalah 'user'
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'user') {
    header("Location: index.php");
    exit;
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_unset();  // Hapus semua data sesi
    session_destroy();  // Hancurkan sesi
    header("Location: index.php");  // Arahkan kembali ke halaman login
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

// Proses tambah ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    // Pastikan user sudah login
    if (!isset($_SESSION['email'])) {
        echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='index.php';</script>";
        exit;
    }

    // Ambil email dari sesi
    $email = $_SESSION['email'];
    $productId = $_POST['product_id'];
    $qty = (int)$_POST['quantity'];

    // Ambil data produk berdasarkan ID
    $queryProduct = "SELECT id, nama, deskripsi, harga, gambar FROM produk WHERE id = :id";
    $stmtProduct = $pdo->prepare($queryProduct);
    $stmtProduct->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmtProduct->execute();
    $product = $stmtProduct->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $total = $product['harga'] * $qty;

        // Masukkan ke tabel keranjang dengan email yang terambil dari sesi
        $queryInsert = "INSERT INTO keranjang (nama_produk, deskripsi, harga, qty, email, total, gambar) 
                        VALUES (:nama_produk, :deskripsi, :harga, :qty, :email, :total, :gambar)";
        $stmtInsert = $pdo->prepare($queryInsert);
        $stmtInsert->execute([
            ':nama_produk' => $product['nama'],
            ':deskripsi' => $product['deskripsi'], // Menambahkan deskripsi produk
            ':harga' => $product['harga'],
            ':qty' => $qty,
            ':email' => $email,  // Menggunakan email dari sesi
            ':total' => $total,
            ':gambar' => $product['gambar'],  // Menambahkan gambar produk
        ]);

        echo "<script>alert('Produk berhasil ditambahkan ke keranjang!'); window.location.href='homeser.php';</script>";
    } else {
        echo "<script>alert('Produk tidak ditemukan!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <!-- Header -->
    <header class="bg-gray-100 text-white p-5 flex justify-between items-center flex-wrap">
        <h1 class="text-2xl text-black font-bold w-full text-center md:w-auto">Ikky Shop</h1>
        <div class="flex items-center w-full md:w-auto justify-between md:space-x-6 mt-4 md:mt-0">
        <!-- Form Pencarian -->
            <form action="" method="GET" class="flex w-full md:w-auto space-x-2">
                <input type="text" name="search" placeholder="Cari Produk..." class="px-4 py-2 rounded-lg text-gray-700 border border-gray-300 w-full">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Cari</button>
            </form>

            <!-- Tombol Logout dengan jarak -->
            <a href="?logout=true" class="bg-red-400 text-white-800 px-4 py-2 rounded-lg flex items-center transition duration-300 ease-in-out hover:bg-red-800 hover:shadow-lg ml-2">
                Logout
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <div class="p-8 flex flex-col md:flex-row space-x-0 md:space-x-8">
        <!-- Sidebar Kategori (Dropdown di Mobile) -->
        <div class="w-full md:w-1/4 bg-gray-100 shadow-md rounded-lg p-4 mb-6 md:mb-0">
            <h3 class="text-xl font-bold text-gray-700 mb-4">Kategori</h3>
            <!-- Dropdown untuk mobile -->
            <div class="md:hidden">
                <select class="w-full p-2 border border-gray-300 rounded" onchange="location = this.value;">
                    <option value="homeser.php">Pilih Kategori</option>
                    <option value="homeser.php">Semua Kategori</option>
                    <?php if (isset($categories) && !empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <option value="?kategori=<?= urlencode($category['kategori']) ?>"><?= htmlspecialchars($category['kategori']) ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option class="text-gray-500" disabled>Kategori tidak tersedia</option>
                    <?php endif; ?>
                </select>
            </div>
            <!-- Kategori di desktop -->
            <ul class="hidden md:block">
                <li class="mb-2">
                    <a href="homeser.php" class="text-blue-500 hover:text-blue-700">Semua Kategori</a>
                </li>
                <?php if (isset($categories) && !empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <li class="mb-2">
                            <a href="?kategori=<?= urlencode($category['kategori']) ?>" class="text-blue-500 hover:text-blue-700"><?= htmlspecialchars($category['kategori']) ?></a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="text-gray-500">Kategori tidak tersedia.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Produk -->
        <div class="w-full md:w-3/4">
            <div class="flex w-full md:w-auto justify-between md:space-x-6 mt-4 md:mt-0">
                <h2 class="text-3xl font-bold text-gray-700 mb-6">Produk Populer</h2>
                <a type="button" href="keranjang.php" class="text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-300 ease-in-out hover:bg-gray-500 hover:shadow-lg">
                    <img src="img/cart.png" alt="Keranjang" class="h-5 w-5">
                    <span class="hidden md:inline text-gray-800 transition duration-300 ease-in-out hover:text-white">Keranjang Saya</span> <!-- Menyembunyikan teks di mobile, tampil di desktop -->
                </a>
            </div><br>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php if (isset($products) && !empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="bg-white shadow-md rounded-lg p-4">
                            <img src="sidebar/file/<?= htmlspecialchars($product['gambar']) ?>" alt="<?= htmlspecialchars($product['nama']) ?>" class="mb-4 w-full">
                            <h4 class="text-lg font-bold text-gray-700"><?= htmlspecialchars($product['nama']) ?></h4>
                            <p class="text-gray-600 mb-4">Rp <?= number_format($product['harga'], 0, ',', '.') ?></p>
                            <div class="flex items-center space-x-4">
                                <!-- Tombol Beli Sekarang -->
                                <a href="beli.php?id=<?= $product['id'] ?>" 
                                   class="bg-yellow-500 text-gray px-4 py-2 rounded-lg flex items-center transition duration-300 ease-in-out hover:bg-gray-300 hover:shadow-lg">
                                   Beli Sekarang
                                </a>
                                <!-- Tombol Keranjang -->
                                <button onclick="openCartModal(<?= $product['id'] ?>, <?= $product['harga'] ?>, 'sidebar/file/<?= htmlspecialchars($product['gambar']) ?>')" 
                                    class="text-white px-4 py-2 rounded-lg flex items-center transition duration-300 ease-in-out hover:bg-yellow-300 hover:shadow-lg">
                                    <img src="img/cart.png" alt="Keranjang" class="h-5 w-5">
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500">Produk tidak tersedia.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Keranjang -->
    <div id="cartModal" class="fixed inset-0 bg-gray-700 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 shadow-lg w-full sm:w-3/4 md:w-1/2 lg:w-1/3 xl:w-1/4">
            <h2 class="text-xl font-bold mb-4">Tambah ke Keranjang</h2>
            <form id="cartForm" method="POST">
                <input type="hidden" name="product_id" id="cartProductId">
                <input type="hidden" name="gambar" id="cartProductImage">

                <!-- Gambar Produk -->
                <img id="cartProductImageDisplay" class="mb-4 w-full rounded-lg" src="" alt="Gambar Produk">

                <!-- Jumlah -->
                <div class="col-span-1">
                    <label for="quantity" class="block text-gray-700">Jumlah:</label>
                    <input type="number" name="quantity" id="cartQuantity" class="border border-gray-300 rounded p-2 w-full" required>
                </div>

                <!-- Total Harga -->
                <p id="cartTotalPrice" class="text-gray-700 font-bold">Total Harga: Rp 0</p>

                <div class="flex justify-end mt-4">
                    <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded-lg mr-2" onclick="closeCartModal()">Batal</button>
                    <button type="submit" name="add_to_cart" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Tambah</button>
                </div>
            </form>
        </div>
    </div>


    <script>
        // Fungsi untuk membuka modal untuk produk
        function openCartModal(productId, productPrice, productImage) {
            document.getElementById('cartProductId').value = productId;
            document.getElementById('cartProductImage').value = productImage;
            document.getElementById('cartProductImageDisplay').src = productImage;
            document.getElementById('cartModal').classList.remove('hidden');
            document.getElementById('cartQuantity').value = 1;
            document.getElementById('cartTotalPrice').textContent = 'Total Harga: Rp ' + productPrice;
        }

        // Fungsi untuk menutup modal
        function closeCartModal() {
            document.getElementById('cartModal').classList.add('hidden');
        }

        // Update total harga berdasarkan jumlah
        document.getElementById('cartQuantity').addEventListener('input', function() {
            var quantity = this.value;
            var price = <?= json_encode($product['harga']) ?>;
            var totalPrice = price * quantity;
            document.getElementById('cartTotalPrice').textContent = 'Total Harga: Rp ' + totalPrice;
        });
    </script>
</body>
</html>
