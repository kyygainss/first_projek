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

// Tangkap kata kunci pencarian
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Query untuk mengambil data riwayat dengan format tanggal_transfer hanya tanggal, bulan, dan tahun
$sql = "SELECT *, DATE(tanggal_transfer) AS tanggal_transfer_formatted FROM riwayat WHERE nama_penerima LIKE :searchTerm GROUP BY tanggal_transfer_formatted ORDER BY tanggal_pesanan DESC";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
$stmt->execute();
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat / Nota</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
    <script>
        // JavaScript untuk menghapus parameter 'search' setelah halaman dimuat ulang
        window.onload = function() {
            const url = new URL(window.location.href);
            url.searchParams.delete('search'); // Menghapus parameter pencarian
            history.replaceState({}, document.title, url.toString()); // Mengubah URL tanpa melakukan refresh halaman
        }
    </script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <!-- Header -->
    <header class="bg-gray-100 text-white p-5 flex justify-between items-center">
        <div class="text-black"><?php include('../templatemenu.php'); ?></div>
    </header>

    <!-- Main Content -->
    <div class="p-8">
        <h2 class="text-3xl font-bold text-gray-700 mb-6">Riwayat / Nota</h2>

        <!-- Form Pencarian -->
        <form method="get" class="mb-4 flex items-center space-x-2 w-full max-w-md ml-0">
            <input type="text" name="search" class="border rounded p-2 w-full" value="<?= htmlspecialchars($searchTerm) ?>" placeholder="Cari nama produk...">
            <button type="submit" class="bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-300 ease-in-out hover:bg-blue-500 hover:shadow-lg">
                Cari
            </button>
        </form>

        <!-- Tabel Riwayat -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border-collapse border border-gray-300">
                <thead>
                    <tr>
                        <th class="py-3 px-4 text-left border-b border-r border-gray-300">Nama Penerima</th>
                        <th class="py-3 px-4 text-left border-b border-r border-gray-300">Alamat Pengiriman</th>
                        <th class="py-3 px-4 text-left border-b border-r border-gray-300 hidden sm:table-cell">Metode Pembayaran</th>
                        <th class="py-3 px-4 text-left border-b border-r border-gray-300 hidden sm:table-cell">Tanggal Pesanan</th>
                        <th class="py-3 px-4 text-left border-b border-r border-gray-300 hidden sm:table-cell">Tanggal Transfer</th>
                        <th class="py-3 px-4 text-left border-b border-r border-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat as $data): ?>
                        <tr class="border hover:bg-gray-100">
                            <td class="py-3 px-4 text-left border-b border-r border-gray-300"><?= htmlspecialchars($data['nama_penerima']) ?></td>
                            <td class="py-3 px-4 text-left border-b border-r border-gray-300"><?= htmlspecialchars($data['alamat_pengiriman']) ?></td>
                            <td class="py-3 px-4 text-left border-b border-r border-gray-300 hidden sm:table-cell"><?= htmlspecialchars($data['metode_pembayaran']) ?></td>
                            <td class="py-3 px-4 text-left border-b border-r border-gray-300 hidden sm:table-cell"><?= htmlspecialchars($data['tanggal_pesanan']) ?></td>
                            <!-- Menampilkan hanya tanggal, bulan, dan tahun dari tanggal_transfer -->
                            <td class="py-3 px-4 text-left border-b border-r border-gray-300 hidden sm:table-cell"><?= htmlspecialchars($data['tanggal_transfer']) ?></td>
                            <td class="py-3 px-4 text-left border-b border-r border-gray-300">
                                <a href="nota.php?id=<?= $data['id'] ?>" class="bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-300 ease-in-out hover:bg-blue-500 hover:shadow-lg">Cetak</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
