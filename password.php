<?php
include 'config.php'; // Koneksi ke database

// Variabel untuk pesan error
$error = "";
$showPasswordForm = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);

        try {
            // Periksa apakah email ada di database
            $checkEmailSql = "SELECT COUNT(*) FROM user WHERE email = :email";
            $checkEmailStmt = $pdo->prepare($checkEmailSql);
            $checkEmailStmt->bindParam(':email', $email);
            $checkEmailStmt->execute();
            $emailExists = $checkEmailStmt->fetchColumn();

            if ($emailExists > 0) {
                // Email ditemukan, tampilkan form password
                $showPasswordForm = true;
            } else {
                $error = "E-Mail Tidak Ditemukan.";
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['new_password'], $_POST['confirm_password'], $_POST['email_hidden'])) {
        $email = trim($_POST['email_hidden']);
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if ($newPassword !== $confirmPassword) {
            $error = "Password dan Konfirmasi Password tidak cocok.";
        } else {
            try {
                // Hash password baru
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Update password di database
                $updateSql = "UPDATE user SET password = :password WHERE email = :email";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->bindParam(':password', $hashedPassword);
                $updateStmt->bindParam(':email', $email);
                $updateStmt->execute();

                if ($updateStmt->rowCount() > 0) {
                    echo "<script>alert('Password berhasil diperbarui!');</script>";
                } else {
                    $error = "Password tidak berubah.";
                }
            } catch (PDOException $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 class="text-xl font-bold text-center mb-4">Update Password</h2>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Form Email -->
        <?php if (!$showPasswordForm): ?>
            <form action="" method="POST" class="space-y-4">
                <div>
                    <label for="email" class="block text-gray-700">Email</label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit"
                        class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition">
                    Verifikasi Email
                </button>
            </form>
        <?php endif; ?>

        <!-- Form Password -->
        <?php if ($showPasswordForm): ?>
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="email_hidden" value="<?php echo htmlspecialchars($email); ?>">
                <div>
                    <label for="new_password" class="block text-gray-700">Password Baru</label>
                    <input type="password" id="new_password" name="new_password" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="confirm_password" class="block text-gray-700">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit"
                        class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition">
                    Update Password
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
