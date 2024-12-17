<?php
session_start();
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
    header("Location: homemin.php"); // Arahkan ke dashboard admin jika sudah login
    exit;
}

// Proses login (misalnya, verifikasi username dan password)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Misalnya, verifikasi username dan password (simplified for example purposes)
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Ganti dengan logika pengecekan username dan password yang benar
    if ($username == 'admin' && $password == 'password') {
        $_SESSION['user_type'] = 'admin'; // Set sesi sebagai admin
        header("Location: homemin.php"); // Arahkan ke dashboard admin
        exit;
    } else {
        $error_message = "Username atau password salah!";
    }
}

if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'user') {
    header("Location: homeser.php");
    exit;
}

// Proses login (misalnya, verifikasi username dan password)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Misalnya, verifikasi username dan password (simplified for example purposes)
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Ganti dengan logika pengecekan username dan password yang benar
    if ($username == 'user' && $password == 'password') {
        $_SESSION['user_type'] = 'user';
        header("Location: homeser.php");
        exit;
    } else {
        $error_message = "Username atau password salah!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center text-gray-700 mb-6">Login</h2>
        <form action="process_login.php" method="POST" class="space-y-4">
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

            <p class="mt-4 text-right text-sm text-gray-600">Lupa Password? 
                <a href="password.php" class="text-blue-500 hover:underline">Ganti Disini</a>
            </p>
            <!-- Tombol Login -->
            <button 
                type="submit" 
                class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Login
            </button>
        </form>
        <p class="mt-4 text-center text-sm text-gray-600">Belum punya akun? 
            <a href="register.php" class="text-blue-500 hover:underline">Daftar</a>
        </p>
    </div>
</body>
</html>
