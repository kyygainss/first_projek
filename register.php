<?php
// Mulai sesi untuk menyimpan data pengguna jika diperlukan
session_start();

// Proses registrasi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $username = $_POST['username'];
    $email = $_POST['email'];
    $nama_panjang = $_POST['nama_panjang'];
    $alamat = $_POST['alamat'];
    $domisili = $_POST['domisili'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Enkripsi password
    $type = 'user'; // Set default type menjadi "user"
    $tanggal = date('Y-m-d'); // Tanggal registrasi otomatis

    // Koneksi ke database
    $conn = new mysqli('localhost', 'root', '', 'tokoonline'); // Sesuaikan nama database Anda

    // Cek koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Query untuk menyimpan data pengguna baru
    $sql = "INSERT INTO user (username, email, nama_panjang, alamat, domisili, password, type, tanggal) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssss', $username, $email, $nama_panjang, $alamat, $domisili, $password, $type, $tanggal);

    if ($stmt->execute()) {
        // Redirect ke halaman login setelah berhasil registrasi
        header("Location: index.php");
        exit;
    } else {
        $error_message = "Gagal mendaftar: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center text-gray-700 mb-6">Register</h2>
        <?php if (!empty($error_message)): ?>
            <div class="mb-4 p-2 bg-red-100 text-red-700 rounded">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <form action="register.php" method="POST" class="space-y-4">
            <!-- Username -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required 
                    class="w-full mt-1 p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Masukkan Username Anda">
            </div>
            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    class="w-full mt-1 p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Masukkan Email Anda">
            </div>
            <!-- Nama Panjang -->
            <div>
                <label for="nama_panjang" class="block text-sm font-medium text-gray-700">Nama Panjang</label>
                <input 
                    type="text" 
                    id="nama_panjang" 
                    name="nama_panjang" 
                    required 
                    class="w-full mt-1 p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Masukkan Nama Panjang Anda">
            </div>
            <!-- Alamat -->
            <div>
                <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
                <input 
                    type="text" 
                    id="alamat" 
                    name="alamat" 
                    required 
                    class="w-full mt-1 p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Masukkan Alamat Anda">
            </div>
            <!-- Domisili -->
            <div>
                <label for="domisili" class="block text-sm font-medium text-gray-700">Domisili</label>
                <input 
                    type="text" 
                    id="domisili" 
                    name="domisili" 
                    required 
                    class="w-full mt-1 p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Masukkan Domisili Anda">
            </div>
            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    class="w-full mt-1 p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Masukkan Password Anda">
            </div>
            <!-- Tombol Daftar -->
            <button 
                type="submit" 
                class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Daftar
            </button>
        </form>
        <p class="mt-4 text-center text-sm text-gray-600">Sudah punya akun? 
            <a href="index.php" class="text-blue-500 hover:underline">Login</a>
        </p>
    </div>
</body>
</html>
