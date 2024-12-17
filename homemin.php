<?php
session_start(); 
// Cek jika user bukan admin, arahkan ke halaman login
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
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

$user_name = isset($_SESSION['nama_panjang']) ? $_SESSION['nama_panjang'] : "User";

// Koneksi ke database menggunakan PDO dari config.php
require_once 'config.php'; // Pastikan config.php sudah benar

// Ambil data penjualan hari ini
$tanggal_hari_ini = date('Y-m-d'); // Format tanggal hari ini
$query = "SELECT COUNT(*) FROM riwayat WHERE DATE(created_at) = :created_at";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':created_at', $tanggal_hari_ini);
$stmt->execute();
$penjualan_hari_ini = $stmt->fetchColumn(); // Ambil hasil jumlah penjualan hari ini

// Ambil data pesanan hari ini
$query_pesanan = "SELECT COUNT(*) FROM pesanan WHERE DATE(tanggal_pesanan) = :tanggal_pesanan";
$stmt_pesanan = $pdo->prepare($query_pesanan);
$stmt_pesanan->bindParam(':tanggal_pesanan', $tanggal_hari_ini);
$stmt_pesanan->execute();
$pesanan_hari_ini = $stmt_pesanan->fetchColumn(); // Ambil hasil jumlah pesanan hari ini

// Ambil data total produk dari tabel produk
$query_produk = "SELECT COUNT(*) FROM produk"; // Menghitung semua produk yang ada
$stmt_produk = $pdo->prepare($query_produk);
$stmt_produk->execute();
$total_produk = $stmt_produk->fetchColumn(); // Ambil hasil jumlah produk

// Query untuk mengambil data penjualan berdasarkan hari (total_harga per tanggal)
$query_penjualan = "SELECT DATE(created_at) as tanggal, SUM(total_harga) as total_penjualan
                    FROM riwayat
                    WHERE created_at >= CURDATE() - INTERVAL 7 DAY
                    GROUP BY DATE(created_at)
                    ORDER BY DATE(created_at) ASC";

$stmt_penjualan = $pdo->prepare($query_penjualan);
$stmt_penjualan->execute();
$penjualan_data = $stmt_penjualan->fetchAll(PDO::FETCH_ASSOC);

// Persiapkan data untuk grafik
$tanggal_labels = [];
$total_penjualan_data = [];

foreach ($penjualan_data as $data) {
    $tanggal_labels[] = $data['tanggal']; // Tanggal penjualan
    $total_penjualan_data[] = (float) $data['total_penjualan']; // Total penjualan per tanggal
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-0 transform bg-blue-900 h-screen p-5 lg:w-1/4 w-1/2 -translate-x-full transition-all duration-300 ease-in-out z-50">
        <div class="flex flex-col w-full">
            <h2 class="text-white text-2xl font-bold mb-8">Admin</h2>
            <ul class="space-y-4">
                <li><a href="#" class="text-gray-300 hover:text-white block">Dashboard</a></li>
                <li><a href="sidebar/kelproduk.php" class="text-gray-300 hover:text-white block">Kelola Produk</a></li>
                <li><a href="sidebar/order.php" class="text-gray-300 hover:text-white block">Kelola Pesanan</a></li>
                <li><a href="reports.php" class="text-gray-300 hover:text-white block">Laporan Penjualan</a></li>
                <li><a href="sidebar/riwayat.php" class="text-gray-300 hover:text-white block">Riwayat / Nota</a></li>
                <li><a href="sidebar/pelanggan.php" class="text-gray-300 hover:text-white block">Kelola Pelanggan</a></li>
                <li><a href="sidebar/user.php" class="text-gray-300 hover:text-white block">Pengguna</a></li>
                <li><a href="?logout=true" class="text-red-400 hover:text-white block">Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Overlay (Gelapkan Area Non-Sidebar) -->
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40"></div>

    <!-- Main Content -->
    <div class="lg:pl-1/2 p-8">
        <!-- Toggle Sidebar Button (Logo Menu) -->
        <button onclick="toggleSidebar()" class="mb-4 text-blue-500 hover:text-blue-700 focus:outline-none flex justify-between items-center mb-6 w-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
            <div class="text-blue-500 hover:text-blue-700">Menu</div>
            <div class="text-gray-600 ml-auto">Halo, <?php echo htmlspecialchars($user_name); ?></div>
        </button>

        <!-- Statistik -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h3 class="text-gray-700 font-bold text-lg">Penjualan Hari Ini</h3>
                <p class="text-2xl text-blue-500 font-semibold"><?php echo $penjualan_hari_ini; ?></p> <!-- Menampilkan jumlah penjualan hari ini -->
            </div>
            <div class="bg-white shadow-md rounded-lg p-6">
                <h3 class="text-gray-700 font-bold text-lg">Pesanan Baru</h3>
                <p class="text-2xl text-green-500 font-semibold"><?php echo $pesanan_hari_ini; ?></p> <!-- Menampilkan jumlah pesanan hari ini -->
            </div>
            <div class="bg-white shadow-md rounded-lg p-6">
                <h3 class="text-gray-700 font-bold text-lg">Total Produk</h3>
                <p class="text-2xl text-purple-500 font-semibold"><?php echo $total_produk; ?></p> <!-- Menampilkan total produk -->
            </div>
        </div>

        <!-- Grafik Penjualan -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-lg font-bold text-gray-700 mb-4">Grafik Penjualan (7 Hari Terakhir)</h2>
            <canvas id="salesChart" height="100"></canvas>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($tanggal_labels); ?>, // Tanggal penjualan
                datasets: [{
                    label: 'Total Penjualan',
                    data: <?php echo json_encode($total_penjualan_data); ?>, // Total penjualan per tanggal
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Function to toggle sidebar visibility (for both mobile and desktop)
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('translate-x-0'); // Show sidebar
            sidebar.classList.toggle('-translate-x-full'); // Hide sidebar
            overlay.classList.toggle('hidden'); // Toggle overlay visibility
        }

        // Close sidebar if overlay is clicked
        document.getElementById('overlay').addEventListener('click', function () {
            toggleSidebar(); // Hide sidebar and overlay
        });

        // Ensure that the sidebar behaves correctly on desktop by toggling it with the button
        const mediaQuery = window.matchMedia('(min-width: 1024px)');
        mediaQuery.addEventListener('change', function () {
            if (mediaQuery.matches) {
                document.getElementById('sidebar').classList.remove('-translate-x-full');
                document.getElementById('sidebar').classList.add('translate-x-0');
            } else {
                document.getElementById('sidebar').classList.add('-translate-x-full');
            }
        });
    </script>
</body>
</html>
