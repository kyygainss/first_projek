<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika user bukan admin, arahkan ke halaman login
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Menghubungkan ke file config.php
include_once('../config.php');

// Proses pencarian berdasarkan nama_penerima
$searchTerm = '';
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $query = "SELECT * FROM laporan WHERE nama_penerima LIKE :searchTerm ORDER BY user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':searchTerm' => '%' . $searchTerm . '%']);
} else {
    $query = "SELECT * FROM laporan ORDER BY user_id";
    $stmt = $pdo->query($query);
}

$laporan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses jika tombol "Lunas" ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_laporan'])) {
    $id_laporan = $_POST['id_laporan'];

    // Ambil data laporan berdasarkan id
    $stmt = $pdo->prepare("SELECT * FROM laporan WHERE id = ?");
    $stmt->execute([$id_laporan]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        // Pindahkan data ke tabel riwayat dengan menyertakan user_id
        $insertQuery = "INSERT INTO riwayat (nama_produk, harga, qty, total_harga, tanggal_pesanan, nama_penerima, alamat_pengiriman, metode_pembayaran, tanggal_transfer, user_id)
                        VALUES (:nama_produk, :harga, :qty, :total_harga, :tanggal_pesanan, :nama_penerima, :alamat_pengiriman, :metode_pembayaran, :tanggal_transfer, :user_id)";
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute([
            ':nama_produk' => $data['nama_produk'],
            ':harga' => $data['harga'],
            ':qty' => $data['qty'],
            ':total_harga' => $data['total_harga'],
            ':tanggal_pesanan' => $data['tanggal_pesanan'],
            ':nama_penerima' => $data['nama_penerima'],
            ':alamat_pengiriman' => $data['alamat_pengiriman'],
            ':metode_pembayaran' => $data['metode_pembayaran'],
            ':tanggal_transfer' => $data['tanggal_transfer'],
            ':user_id' => $data['user_id']
        ]);

        // Update kondisi di tabel laporan menjadi 'Selesai'
        $updateQuery = "UPDATE laporan SET kondisi = 'Selesai' WHERE id = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$id_laporan]);

        // Ambil nama_produk dan nama_penerima untuk popup
        $nama_produk = $data['nama_produk'];
        $nama_penerima = $data['nama_penerima'];

        // Kirimkan hasil sebagai response
        echo json_encode([
            'status' => 'success',
            'nama_produk' => $nama_produk,
            'nama_penerima' => $nama_penerima
        ]);
        exit;
    }
}

// Proses jika tombol "Lunas Semua" ditekan untuk semua laporan dalam user_id yang sama
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // Ambil semua laporan berdasarkan user_id
    $stmt = $pdo->prepare("SELECT * FROM laporan WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $laporanData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($laporanData as $data) {
        // Pindahkan data ke tabel riwayat dengan menyertakan user_id
        $insertQuery = "INSERT INTO riwayat (nama_produk, harga, qty, total_harga, tanggal_pesanan, nama_penerima, alamat_pengiriman, metode_pembayaran, tanggal_transfer, user_id)
                        VALUES (:nama_produk, :harga, :qty, :total_harga, :tanggal_pesanan, :nama_penerima, :alamat_pengiriman, :metode_pembayaran, :tanggal_transfer, :user_id)";
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute([
            ':nama_produk' => $data['nama_produk'],
            ':harga' => $data['harga'],
            ':qty' => $data['qty'],
            ':total_harga' => $data['total_harga'],
            ':tanggal_pesanan' => $data['tanggal_pesanan'],
            ':nama_penerima' => $data['nama_penerima'],
            ':alamat_pengiriman' => $data['alamat_pengiriman'],
            ':metode_pembayaran' => $data['metode_pembayaran'],
            ':tanggal_transfer' => $data['tanggal_transfer'],
            ':user_id' => $data['user_id']
        ]);

        // Update kondisi di tabel laporan menjadi 'Selesai'
        $updateQuery = "UPDATE laporan SET kondisi = 'Selesai' WHERE id = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$data['id']]);
    }

    // Kirimkan hasil sebagai response, ganti notifikasi dengan nama produk
    echo json_encode([
        'status' => 'success',
        'message' => 'Pesanan untuk produk "' . $data['nama_produk'] . '" sudah dilunasi.'
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function lunas(id) {
            const formData = new FormData();
            formData.append('id_laporan', id);
            
            fetch('laporan.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.nama_produk + " Sudah Dibayar Oleh " + data.nama_penerima);
                    document.getElementById('btn-lunas-' + id).innerText = 'Dilunasi';
                    document.getElementById('btn-lunas-' + id).classList.replace('bg-green-500', 'bg-gray-500');
                    document.getElementById('btn-lunas-' + id).disabled = true;
                    window.location.reload();
                }
            });
        }

        function lunasSemua(userId) {
            const formData = new FormData();
            formData.append('user_id', userId);
            
            fetch('laporan.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    window.location.reload();
                }
            });
        }

        // JavaScript untuk menghapus parameter 'search' setelah halaman dimuat ulang
        window.onload = function() {
            const url = new URL(window.location.href);
            url.searchParams.delete('search'); // Menghapus parameter pencarian
            history.replaceState({}, document.title, url.toString()); // Mengubah URL tanpa melakukan refresh halaman
        }
    </script>
    <style>
        /* Media Query untuk perangkat mobile */
        @media (max-width: 640px) {
            .hide-on-mobile {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <!-- Header -->
    <header class="bg-gray-100 text-white p-5 flex justify-between items-center">
        <div class="text-black"><?php include('../templatemenu.php'); ?></div>
    </header>

    <!-- Main Content -->
    <div class="p-8">
        <h2 class="text-3xl font-bold text-gray-700 mb-6">Laporan Penjualan</h2>

        <!-- Form Pencarian -->
        <form method="get" class="mb-4 flex items-center space-x-2 w-full max-w-xs">
            <input type="text" name="search" class="border rounded p-2 w-full" value="<?= htmlspecialchars($searchTerm) ?>" placeholder="Cari nama penerima...">
            <button type="submit" class="bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-300 ease-in-out hover:bg-blue-500 hover:shadow-lg">
                Cari
            </button>
        </form>

        <table class="min-w-full bg-white border-collapse border border-gray-300">
            <thead>
                <tr>
                    <th class="py-3 px-4 text-left border-b border-r border-gray-300">Nama Produk</th>
                    <th class="py-3 px-4 text-left border-b border-r border-gray-300">Harga</th>
                    <th class="py-3 px-4 text-left hide-on-mobile border-b border-r border-gray-300">Jumlah</th>
                    <th class="py-3 px-4 text-left hide-on-mobile border-b border-r border-gray-300">Total Harga</th>
                    <th class="py-3 px-4 text-left hide-on-mobile border-b border-r border-gray-300">Tanggal Pesanan</th>
                    <th class="py-3 px-4 text-left hide-on-mobile border-b border-r border-gray-300">Nama Penerima</th>
                    <th class="py-3 px-4 text-left hide-on-mobile border-b border-r border-gray-300">Alamat Pengiriman</th>
                    <th class="py-3 px-4 text-left hide-on-mobile border-b border-r border-gray-300">Metode Pembayaran</th>
                    <th class="py-3 px-4 text-left hide-on-mobile border-b border-r border-gray-300">Tanggal Transfer</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Laporan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $current_user_id = null;
                    foreach ($laporan as $data):
                        // Menyembunyikan baris User ID jika kondisi sudah Selesai
                        if ($data['kondisi'] != 'Selesai' && $current_user_id !== $data['user_id']): ?>
                            <tr class="border hover:bg-gray-100">
                                <td colspan="10" class="py-3 px-4 border-r border-gray-300">
                                    <!-- Tombol Lunas Semua di bawah tabel -->
                                    <div class="flex justify-end">
                                        <button onclick="lunasSemua(<?= $data['user_id'] ?>)" class="bg-green-400 text-white px-4 py-2 rounded-lg flex items-center transition duration-300 ease-in-out hover:bg-green-700 hover:shadow-lg ml-2">Lunas Semua</button>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                            $current_user_id = $data['user_id']; 
                        endif;
                        ?>

                        <tr class="border hover:bg-gray-100">
                            <td class="py-3 px-4 border-r border border-gray-300"><?= $data['nama_produk'] ?></td>
                            <td class="py-3 px-4 border-r border border-gray-300"><?= $data['harga'] ?></td>
                            <td class="py-3 px-4 border-r border border-gray-300 hide-on-mobile"><?= $data['qty'] ?></td>
                            <td class="py-3 px-4 border-r border border-gray-300 hide-on-mobile"><?= $data['total_harga'] ?></td>
                            <td class="py-3 px-4 border-r border border-gray-300 hide-on-mobile"><?= $data['tanggal_pesanan'] ?></td>
                            <td class="py-3 px-4 border-r border border-gray-300 hide-on-mobile"><?= $data['nama_penerima'] ?></td>
                            <td class="py-3 px-4 border-r border border-gray-300 hide-on-mobile"><?= $data['alamat_pengiriman'] ?></td>
                            <td class="py-3 px-4 border-r border border-gray-300 hide-on-mobile"><?= $data['metode_pembayaran'] ?></td>
                            <td class="py-3 px-4 border-r border border-gray-300 hide-on-mobile"><?= $data['tanggal_transfer'] ?></td>
                            <td class="py-3 px-4 border-r border border-gray-300">
                                <?php if ($data['kondisi'] != 'Selesai'): ?>
                                    <a id="btn-lunas-<?= $data['id'] ?>" class="bg-gray-400 text-white-800 px-4 py-2 rounded-lg flex items-center transition duration-300 ease-in-out hover:bg-gray-300 hover:shadow-lg ml-2" onclick="lunas(<?= $data['id'] ?>)">Proses</a>
                                <?php else: ?>
                                    <span class="text-green-700">Dilunasi</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
