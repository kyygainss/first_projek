<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika user bukan admin, arahkan ke halaman login
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit;
}

require_once '../config.php';

// Variabel untuk pencarian
$searchKeyword = '';
if (isset($_POST['search'])) {
    $searchKeyword = trim($_POST['searchKeyword']);
}

// Query untuk mengambil data pesanan
$queryPesanan = "SELECT * FROM pesanan";
if (!empty($searchKeyword)) {
    $queryPesanan .= " WHERE nama_penerima LIKE :keyword";
}
$queryPesanan .= " ORDER BY user_id";

$stmtPesanan = $pdo->prepare($queryPesanan);

// Menyiapkan parameter pencarian jika ada
if (!empty($searchKeyword)) {
    $searchParam = "%$searchKeyword%";
    $stmtPesanan->bindParam(':keyword', $searchParam, PDO::PARAM_STR);
}

$stmtPesanan->execute();
$pesanan = $stmtPesanan->fetchAll(PDO::FETCH_ASSOC);

// Mengelompokkan data pesanan berdasarkan user_id
$groupedPesanan = [];
foreach ($pesanan as $order) {
    $groupedPesanan[$order['user_id']][] = $order;
}

// Proses aksi (konfirmasi/batal)
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    $stmtPesananDetail = $pdo->prepare("SELECT * FROM pesanan WHERE id = :id");
    $stmtPesananDetail->bindValue(':id', $id, PDO::PARAM_INT);
    $stmtPesananDetail->execute();
    $order = $stmtPesananDetail->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        $kondisi = ($action === 'konfirmasi') ? 'Diproses' : 'Dibatalkan';

        $stmtInsertLaporan = $pdo->prepare(
            "INSERT INTO laporan 
            (nama_produk, harga, qty, total_harga, tanggal_pesanan, nama_penerima, alamat_pengiriman, metode_pembayaran, catatan, kondisi, user_id)
            VALUES 
            (:nama_produk, :harga, :qty, :total_harga, :tanggal_pesanan, :nama_penerima, :alamat_pengiriman, :metode_pembayaran, :catatan, :kondisi, :user_id)"
        );
        $stmtInsertLaporan->execute([
            ':nama_produk' => $order['nama_produk'],
            ':harga' => $order['harga'],
            ':qty' => $order['qty'],
            ':total_harga' => $order['total_harga'],
            ':tanggal_pesanan' => $order['tanggal_pesanan'],
            ':nama_penerima' => $order['nama_penerima'],
            ':alamat_pengiriman' => $order['alamat_pengiriman'],
            ':metode_pembayaran' => $order['metode_pembayaran'],
            ':catatan' => $order['catatan'],
            ':kondisi' => $kondisi,
            ':user_id' => $order['user_id']
        ]);

        $stmtDeletePesanan = $pdo->prepare("DELETE FROM pesanan WHERE id = :id");
        $stmtDeletePesanan->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtDeletePesanan->execute();

        echo "<script>alert('Pesanan telah $kondisi!'); window.location.href = 'order.php';</script>";
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya</title>
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
        <div><?php include('../templatemenu.php'); ?></div>
    </header>

    <!-- Main Content -->
    <div class="p-8">
        <h2 class="text-3xl font-bold text-gray-700 mb-6">Daftar Pesanan</h2>

        <!-- Form Pencarian -->
        <form method="POST" class="mb-4 flex items-center space-x-2 w-full max-w-xs">
            <div class="flex items-center p-2 w-full">
                <input 
                    type="text" 
                    name="searchKeyword" 
                    placeholder="Cari nama penerima..." 
                    value="<?php echo htmlspecialchars($searchKeyword); ?>" 
                    class="border border-gray-300 p-2 w-full rounded-lg focus:outline-none focus:ring focus:border-blue-300">
                <button 
                    type="submit" 
                    name="search"
                    class="bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-300 ease-in-out hover:bg-blue-500 hover:shadow-lg">
                    Cari
                </button>
            </div>
        </form>

        <!-- Tabel Pesanan -->
        <table class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr>
                    <th class="py-2 px-4 text-left border md:hidden">Produk</th>
                    <th class="py-2 px-4 text-left border md:hidden">Hargs</th>
                    <th class="py-2 px-4 text-left border md:hidden">Aksi</th>
                    <th class="py-2 px-4 text-left border hidden md:table-cell">Produk</th>
                    <th class="py-2 px-4 text-left border hidden md:table-cell">Harga</th>
                    <th class="py-2 px-4 text-left border hidden md:table-cell">Jumlah</th>
                    <th class="py-2 px-4 text-left border hidden md:table-cell">Total Harga</th>
                    <th class="py-2 px-4 text-left border hidden md:table-cell">Tanggal Pesan</th>
                    <th class="py-2 px-4 text-left border hidden md:table-cell">Nama Penerima</th>
                    <th class="py-2 px-4 text-left border hidden md:table-cell">Alamat Pengiriman</th>
                    <th class="py-2 px-4 text-left border hidden md:table-cell">Metode Pembayaran</th>
                    <th class="py-2 px-4 text-left border hidden md:table-cell">Catatan</th>
                    <th class="py-2 px-4 text-left border hidden md:table-cell">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pesanan as $order): ?>
                    <tr class="border hover:bg-gray-100">
                        <td class="py-2 px-4 border md:hidden"><?= htmlspecialchars($order['nama_produk']) ?></td>
                        <td class="py-2 px-4 border md:hidden">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></td>
                        <td class="py-2 px-4 border md:hidden">
                            <?php if ($order['kondisi'] == 'Menunggu'): ?>
                                <a href="?action=konfirmasi&id=<?= $order['id'] ?>" class="text-green-500 hover:text-green-700">Konfirmasi</a> | 
                                <a href="?action=batal&id=<?= $order['id'] ?>" class="text-red-500 hover:text-red-700">Batal</a>
                            <?php else: ?>
                                <span class="text-gray-500"><?= htmlspecialchars($order['kondisi']) ?></span>
                            <?php endif; ?>
                        </td>

                        <td class="py-2 px-4 border hidden md:table-cell"><?= htmlspecialchars($order['nama_produk']) ?></td>
                        <td class="py-2 px-4 border hidden md:table-cell">Rp <?= number_format($order['harga'], 0, ',', '.') ?></td>
                        <td class="py-2 px-4 border hidden md:table-cell"><?= htmlspecialchars($order['qty']) ?></td>
                        <td class="py-2 px-4 border hidden md:table-cell">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></td>
                        <td class="py-2 px-4 border hidden md:table-cell"><?= htmlspecialchars($order['tanggal_pesanan']) ?></td>
                        <td class="py-2 px-4 border hidden md:table-cell"><?= htmlspecialchars($order['nama_penerima']) ?></td>
                        <td class="py-2 px-4 border hidden md:table-cell"><?= htmlspecialchars($order['alamat_pengiriman']) ?></td>
                        <td class="py-2 px-4 border hidden md:table-cell"><?= htmlspecialchars($order['metode_pembayaran']) ?></td>
                        <td class="py-2 px-4 border hidden md:table-cell"><?= htmlspecialchars($order['catatan']) ?></td>
                        <td class="py-2 px-4 border hidden md:table-cell">
                            <?php if ($order['kondisi'] == 'Menunggu'): ?>
                                <a href="?action=konfirmasi&id=<?= $order['id'] ?>" class="text-green-500 hover:text-green-700">Konfirmasi</a> | 
                                <a href="?action=batal&id=<?= $order['id'] ?>" class="text-red-500 hover:text-red-700">Batal</a>
                            <?php else: ?>
                                <span class="text-gray-500"><?= htmlspecialchars($order['kondisi']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
