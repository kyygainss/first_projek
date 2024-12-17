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

// Query untuk mengambil data pelanggan, mengelompokkan berdasarkan nama_penerima, dan mengambil tanggal yang paling awal
$sql = "SELECT nama_penerima, alamat_pengiriman, MIN(created_at) AS earliest_date
        FROM riwayat
        WHERE nama_penerima LIKE :searchTerm
        GROUP BY nama_penerima, alamat_pengiriman
        ORDER BY earliest_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
$stmt->execute();
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cek apakah data ditemukan
if (empty($riwayat)) {
    $message = 'Data tidak ditemukan.';
} else {
    $message = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pelanggan</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
    <header class="bg-gray-100 p-5 flex justify-between items-center">
        <div class="text-black"><?php include('../templatemenu.php'); ?></div>
    </header>

    <!-- Main Content -->
    <div class="p-8">
        <h2 class="text-3xl font-bold text-gray-700 mb-6">Kelola Pelanggan</h2>

        <!-- Form Pencarian -->
        <form method="get" class="mb-4 flex items-center space-x-2 w-full max-w-md">
            <input type="text" name="search" class="border rounded p-2 w-full" value="<?= htmlspecialchars($searchTerm) ?>" placeholder="Cari nama pelanggan...">
            <button type="submit" class="bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-300 hover:bg-blue-500 hover:shadow-lg">
                Cari
            </button>
        </form>

        <!-- Pesan jika data tidak ditemukan -->
        <?php if ($message): ?>
            <p class="text-red-500"><?= $message ?></p>
        <?php endif; ?>

        <!-- Tabel Pelanggan -->
        <div class="overflow-x-auto">
            <table class="table-auto min-w-full bg-white border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-3 px-4 text-left border-b border-gray-300">Nama</th>
                        <th class="py-3 px-4 text-left border-b border-gray-300">Alamat</th>
                        <th class="py-3 px-4 text-left border-b border-gray-300 hidden md:table-cell">Tanggal Bergabung</th>
                        <th class="py-3 px-4 text-left border-b border-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat as $data): ?>
                        <tr class="border hover:bg-gray-100">
                            <td class="py-3 px-4 text-left"><?= htmlspecialchars($data['nama_penerima']) ?></td>
                            <td class="py-3 px-4 text-left"><?= htmlspecialchars($data['alamat_pengiriman']) ?></td>
                            <td class="py-3 px-4 text-left hidden md:table-cell"><?= htmlspecialchars($data['earliest_date']) ?></td>
                            <td class="py-3 px-4 text-left">
                                <a href="edit_pelanggan.php?nama_penerima=<?= urlencode($data['nama_penerima']) ?>" class="bg-green-400 text-white px-4 py-2 rounded-lg transition hover:bg-green-700">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
