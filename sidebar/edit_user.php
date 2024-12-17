<?php
// File: edit_user.php
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

// Tangkap ID user yang dikirimkan melalui URL
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Query untuk mengambil data user berdasarkan ID
$sql = "SELECT id, username, email, nama_panjang, alamat, domisili, type, tanggal FROM user WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika data user tidak ditemukan
if (!$user) {
    die('Data tidak ditemukan.');
}

// Proses update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $username = $_POST['username'];
    $email = $_POST['email'];
    $nama_panjang = $_POST['nama_panjang'];
    $alamat = $_POST['alamat'];
    $domisili = $_POST['domisili'];
    $type = $_POST['type'];

    // Update data user
    $updateSql = "UPDATE user 
                  SET username = :username, email = :email, nama_panjang = :nama_panjang, alamat = :alamat, domisili = :domisili, type = :type 
                  WHERE id = :id";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->bindValue(':username', $username);
    $updateStmt->bindValue(':email', $email);
    $updateStmt->bindValue(':nama_panjang', $nama_panjang);
    $updateStmt->bindValue(':alamat', $alamat);
    $updateStmt->bindValue(':domisili', $domisili);
    $updateStmt->bindValue(':type', $type);
    $updateStmt->bindValue(':id', $id, PDO::PARAM_INT);
    $updateStmt->execute();

    // Redirect setelah update
    header('Location: user.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal flex items-center justify-center min-h-screen">

    <!-- Card Container -->
    <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-lg">
        <h2 class="text-2xl font-bold text-gray-700 mb-6 text-center">Edit User</h2>

        <!-- Form Edit User -->
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-gray-700 mb-2">Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="border rounded-lg p-3 w-full focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label class="block text-gray-700 mb-2">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="border rounded-lg p-3 w-full focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label class="block text-gray-700 mb-2">Nama Panjang</label>
                <input type="text" name="nama_panjang" value="<?= htmlspecialchars($user['nama_panjang']) ?>" class="border rounded-lg p-3 w-full focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label class="block text-gray-700 mb-2">Alamat</label>
                <input type="text" name="alamat" value="<?= htmlspecialchars($user['alamat']) ?>" class="border rounded-lg p-3 w-full focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label class="block text-gray-700 mb-2">Domisili</label>
                <input type="text" name="domisili" value="<?= htmlspecialchars($user['domisili']) ?>" class="border rounded-lg p-3 w-full focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label class="block text-gray-700 mb-2">Type</label>
                <select name="type" class="border rounded-lg p-3 w-full focus:ring-2 focus:ring-blue-500" required>
                    <option value="admin" <?= $user['type'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="user" <?= $user['type'] === 'user' ? 'selected' : '' ?>>User</option>
                </select>
            </div>

            <div class="flex justify-between">
                <a href="user.php" class="bg-red-500 text-white px-4 py-2 rounded-lg transition duration-300 ease-in-out hover:bg-red-700 hover:shadow-lg">Kembali</a>
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">Simpan Perubahan</button>
            </div>
        </form>
    </div>

</body>
</html>
