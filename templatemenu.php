<?php
// Periksa apakah sesi sudah dimulai, jika belum, maka jalankan session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek jika user bukan admin, arahkan ke halaman login
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_unset();  // Hapus semua data sesi
    session_destroy();  // Hancurkan sesi
    header("Location: ../index.php");  // Arahkan kembali ke halaman login
    exit;
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
                <li><a href="../homemin.php" class="text-gray-300 hover:text-white block">Dashboard</a></li>
                <li><a href="kelproduk.php" class="text-gray-300 hover:text-white block">Kelola Produk</a></li>
                <li><a href="order.php" class="text-gray-300 hover:text-white block">Kelola Pesanan</a></li>
                <li><a href="laporan.php" class="text-gray-300 hover:text-white block">Laporan Penjualan</a></li>
                <li><a href="riwayat.php" class="text-gray-300 hover:text-white block">Riwayat / Nota</a></li>
                <li><a href="pelanggan.php" class="text-gray-300 hover:text-white block">Kelola Pelanggan</a></li>
                <li><a href="user.php" class="text-gray-300 hover:text-white block">Pengguna</a></li>
                </li>
                <li><a href="?logout=true" class="text-red-400 hover:text-white block">Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Overlay (Gelapkan Area Non-Sidebar) -->
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40"></div>

    <!-- Main Content -->
    <div class="lg:pl-1/2 p-8">
        <!-- Toggle Sidebar Button (Logo Menu) -->

        <button onclick="toggleSidebar()" class="mb-4 text-gray-500 hover:text-blue-700 focus:outline-none flex justify-between items-center mb-6 w-full">
            <!-- Hamburger Menu Icon (Logo Menu) -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>Menu
        </button>
    </div>

    <!-- Chart.js Script -->
    <script>
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
