<?php
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['email'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='index.php';</script>";
    exit;
}

// Ambil email dari sesi
$email = $_SESSION['email'];

// Koneksi ke database
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Koneksi gagal: " . $e->getMessage();
}

// Cek apakah id diterima di URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Query untuk mengambil data berdasarkan id
    $query = "SELECT id, nama_produk, harga, qty, total, gambar FROM keranjang WHERE id = :id AND email = :email";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Cek apakah data ditemukan
    if (!$data) {
        echo "<script>alert('Data tidak ditemukan!'); window.location.href='keranjang.php';</script>";
        exit;
    }

    // Proses form jika submit checkout
    if (isset($_POST['checkout'])) {
        $nama_penerima = $_POST['nama_penerima'];
        $alamat_pengiriman = $_POST['alamat_pengiriman'];
        $qty = $_POST['qty'];
        $catatan = $_POST['catatan']; // Menambahkan catatan untuk seller
        $metode_pembayaran = $_POST['metode_pembayaran']; // Ambil nilai metode pembayaran dari form
        $total = $data['harga'] * $qty; // Hitung total harga sesuai jumlah

        if ($qty <= 0) {
            // Hapus data dari keranjang jika qty kosong atau nol
            $queryDelete = "DELETE FROM keranjang WHERE id = :id";
            $stmtDelete = $pdo->prepare($queryDelete);
            $stmtDelete->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtDelete->execute();
            echo "<script>alert('Produk dihapus karena jumlahnya tidak valid!'); window.location.href='keranjang.php';</script>";
            exit;
        }

        // Ambil id_user berdasarkan email
        $queryUser = "SELECT id FROM user WHERE email = :email";
        $stmtUser = $pdo->prepare($queryUser);
        $stmtUser->bindParam(':email', $email, PDO::PARAM_STR);
        $stmtUser->execute();
        $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            $user_id = $userData['id'];

            // Query untuk memasukkan data ke tabel pesanan
            $queryInsert = "INSERT INTO pesanan (user_id, produk_id, nama_produk, harga, qty, total_harga, alamat_pengiriman, kondisi, tanggal_pesanan, metode_pembayaran, nama_penerima, nomor_telepon, catatan)
                            VALUES (:user_id, :produk_id, :nama_produk, :harga, :qty, :total_harga, :alamat_pengiriman, 'Menunggu', NOW(), :metode_pembayaran, :nama_penerima, :nomor_telepon, :catatan)";
            $stmtInsert = $pdo->prepare($queryInsert);
            $stmtInsert->execute([
                ':user_id' => $user_id,
                ':produk_id' => $id,
                ':nama_produk' => $data['nama_produk'],
                ':harga' => $data['harga'],
                ':qty' => $qty,
                ':total_harga' => $total,
                ':alamat_pengiriman' => $alamat_pengiriman,
                ':nama_penerima' => $nama_penerima,
                ':metode_pembayaran' => $metode_pembayaran, // Pastikan ada nilai metode pembayaran
                ':nomor_telepon' => $_POST['nomor_telepon'], // Pastikan field nomor telepon ada di form
                ':catatan' => $catatan
            ]);

            // Mengurangi qty di tabel keranjang
            $newQty = $data['qty'] - $qty;
            if ($newQty <= 0) {
                $queryDeleteKeranjang = "DELETE FROM keranjang WHERE id = :id";
                $stmtDeleteKeranjang = $pdo->prepare($queryDeleteKeranjang);
                $stmtDeleteKeranjang->bindParam(':id', $id, PDO::PARAM_INT);
                $stmtDeleteKeranjang->execute();
            } else {
                $queryUpdateKeranjang = "UPDATE keranjang SET qty = :qty WHERE id = :id";
                $stmtUpdateKeranjang = $pdo->prepare($queryUpdateKeranjang);
                $stmtUpdateKeranjang->execute([ ':qty' => $newQty, ':id' => $id ]);
            }

            echo "<script>alert('Checkout berhasil!'); window.location.href='keranjang.php';</script>";
            exit;
        } else {
            echo "<script>alert('User tidak ditemukan!'); window.location.href='keranjang.php';</script>";
            exit;
        }
    }
} else {
    echo "<script>alert('ID tidak ditemukan!'); window.location.href='keranjang.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Menambahkan styling untuk memusatkan form */
        .checkout-form {
            max-width: 600px; /* Mengatur lebar form */
            margin: 0 auto; /* Memusatkan form */
        }
    </style>
</head>
<body class="bg-gray-200">
    <div class="min-h-screen flex items-center justify-center bg-gray-200">
        <div class="container p-6 bg-white rounded shadow-lg w-full max-w-lg">
            <h2 class="text-2xl font-semibold mb-4 text-center">Checkout</h2>
            <form action="" method="POST">
                <!-- Nama Penerima -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium" for="nama_penerima">Nama Penerima</label>
                    <input type="text" id="nama_penerima" name="nama_penerima" class="w-full p-2 border rounded" required>
                </div>

                <!-- Data Harga -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium" for="nomor_telepon">Telepon / Whatsapp</label>
                    <input type="text" id="nomor_telepon" name="nomor_telepon" class="w-full p-2 border rounded">
                </div>

                <!-- Jumlah Input -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium" for="qty">Jumlah</label>
                    <input type="number" id="qty" name="qty" class="w-full p-2 border rounded" min="1" value="" max="<?= $data['qty']; ?>" required>
                    <div id="qty-error" class="text-red-500 text-sm mt-2" style="display: none;">Keranjang Tidak Mencukupi</div>
                </div>

                <!-- Alamat Pengiriman -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium" for="alamat_pengiriman">Alamat Pengiriman</label>
                    <textarea id="alamat_pengiriman" name="alamat_pengiriman" class="w-full p-2 border rounded" required></textarea>
                </div>

                <!-- Catatan untuk Seller -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium" for="catatan">Catatan untuk Seller</label>
                    <textarea id="catatan" name="catatan" class="w-full p-2 border rounded"></textarea>
                </div>

                <!-- Total Harga -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium" for="total">Total Harga</label>
                    <input type="text" id="total" name="total" class="w-full p-2 border rounded bg-gray-100" readonly>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium" for="metode_pembayaran">Metode Pembayaran</label>
                    <select id="metode_pembayaran" name="metode_pembayaran" class="w-full p-2 border border-gray-300 rounded" required>
                        <option value="">Pilih Metode Pembayaran</option>
                        <option value="COD">Cash On Delivery</option>
                    </select>
                </div>

                <!-- Tombol Submit -->
                <div class="flex justify-end">
                    <button type="submit" name="checkout" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600">Checkout</button>
                    <a href="keranjang.php" class="ml-4 bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Script Hitung Total dan Validasi Jumlah -->
    <script>
        document.getElementById('qty').addEventListener('input', function () {
            let harga = <?= $data['harga']; ?>;
            let qty = this.value;
            let maxQty = <?= $data['qty']; ?>;
            let totalElement = document.getElementById('total');
            let errorElement = document.getElementById('qty-error');

            // Hitung total harga
            totalElement.value = harga * qty;

            // Cek apakah jumlah melebihi stok yang tersedia
            if (qty > maxQty) {
                errorElement.style.display = 'block';
            } else {
                errorElement.style.display = 'none';
            }
        });
    </script>
</body>
</html>
