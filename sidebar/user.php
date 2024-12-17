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

// Query untuk mendapatkan semua data user berdasarkan username, diurutkan berdasarkan ID secara berurutan
$sql = "SELECT id, username, email, nama_panjang, alamat, domisili, type, tanggal 
        FROM user 
        WHERE username LIKE :searchTerm
        ORDER BY id ASC"; 

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cek apakah data ditemukan
if (empty($users)) {
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
    <title>Daftar User</title>
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
<body class="bg-gray-100 font-sans leading-normal tracking-normal p-6">

    <header class="bg-gray-100 p-5 flex justify-between items-center">
        <div class="text-black"><?php include('../templatemenu.php'); ?></div>
    </header>

    <div class="container mx-auto">
        <h1 class="text-3xl font-bold text-gray-700 mb-6">Daftar User</h1>

        <!-- Form Pencarian -->
        <form method="get" class="mb-4 flex items-center space-x-2 w-full max-w-md">
            <input type="text" name="search" class="border rounded p-2 w-full" value="<?= htmlspecialchars($searchTerm) ?>" placeholder="Cari username...">
            <button type="submit" class="bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-300 hover:bg-blue-500 hover:shadow-lg">
                Cari
            </button>
        </form>

        <!-- Pesan jika data tidak ditemukan -->
        <?php if ($message): ?>
            <p class="text-red-500 mb-4"><?= $message ?></p>
        <?php endif; ?>

        <!-- Tabel Responsif -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-2 px-4 text-left border hidden md:table-cell">ID</th>
                        <th class="py-2 px-4 text-left border md:hidden">User</th>
                        <th class="py-2 px-4 text-left border md:hidden">Email</th>
                        <th class="py-2 px-4 text-left border hidden md:table-cell">Nama Panjang</th>
                        <th class="py-2 px-4 text-left border hidden md:table-cell">Alamat</th>
                        <th class="py-2 px-4 text-left border hidden md:table-cell">Domisili</th>
                        <th class="py-2 px-4 text-left border hidden md:table-cell">Type</th>
                        <th class="py-2 px-4 text-left border hidden md:table-cell">Tanggal</th>
                        <th class="py-2 px-4 text-left border">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="border hover:bg-gray-100">
                            <td class="py-2 px-4 text-left border hidden md:table-cell"><?= htmlspecialchars($user['id']) ?></td>
                            <td class="py-2 px-4 text-left border md:hidden"><?= htmlspecialchars($user['username']) ?></td>
                            <td class="py-2 px-4 text-left border md:hidden"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="py-2 px-4 text-left border hidden md:table-cell"><?= htmlspecialchars($user['nama_panjang']) ?></td>
                            <td class="py-2 px-4 text-left border hidden md:table-cell"><?= htmlspecialchars($user['alamat']) ?></td>
                            <td class="py-2 px-4 text-left border hidden md:table-cell"><?= htmlspecialchars($user['domisili']) ?></td>
                            <td class="py-2 px-4 text-left border hidden md:table-cell"><?= htmlspecialchars($user['type']) ?></td>
                            <td class="py-2 px-4 text-left border hidden md:table-cell"><?= htmlspecialchars($user['tanggal']) ?></td>
                            <td class="py-2 px-4 text-left border">
                                <a href="edit_user.php?id=<?= htmlspecialchars($user['id']) ?>" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-700">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
