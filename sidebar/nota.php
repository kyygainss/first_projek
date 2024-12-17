<?php
// Include config.php untuk koneksi ke database
include('../config.php');

// Load DOMPDF
require_once '../vendor/autoload.php'; // Jika menggunakan Composer
// require_once '../dompdf/autoload.inc.php'; // Jika menggunakan pengunduhan manual

use Dompdf\Dompdf;
use Dompdf\Options;

// Mengambil ID dari URL
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Query untuk mengambil semua data berdasarkan nama_penerima dan tanggal_transfer yang sama (tanpa waktu)
$sql = "
    SELECT * 
    FROM riwayat 
    WHERE nama_penerima = (SELECT nama_penerima FROM riwayat WHERE id = :id) 
    AND DATE(tanggal_transfer) = (SELECT DATE(tanggal_transfer) FROM riwayat WHERE id = :id)
";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($data) {
    // Mengatur opsi untuk DOMPDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $dompdf = new Dompdf($options);

    // HTML untuk nota dengan dua tabel, kata pengantar dan penutup
    $html = '
    <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Nota</title>
            <style>
                body { font-family: Arial, sans-serif; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
                th { background-color: #f4f4f4; }
                .table-header { background-color: #f4f4f4; font-weight: bold; }
                .table-2 td { padding-left: 20px; padding-right: 20px; background-color: #f4f4f4; }
                h2 { font-size: 18px; margin-bottom: 10px; }
                .footer { margin-top: 20px; text-align: center; font-style: italic; }
                .promotions { margin-top: 20px; font-size: 16px; font-weight: bold; color: #d9534f; }
            </style>
        </head>
        <body>
            <h1 style="text-align: center; font-family: fantasy;">Ikyy Store</h1>
            <h2>Terima kasih telah berbelanja di toko kami. Berikut adalah detail transaksi Anda:</h2><br>

            <table>
                <tr class="table-header">
                    <th style="text-align: left;">Nama Produk</th>
                    <th style="text-align: left;">Jumlah</th>
                    <th style="text-align: left;">Harga</th>
                    <th style="text-align: left;">Total Harga</th>
                </tr>';

    // Menampilkan semua data yang diambil
    $totalBayar = 0;
    foreach ($data as $item) {
        $totalBayar += $item['total_harga']; // Menjumlahkan total harga
        $html .= '
        <tr>
            <td>' . htmlspecialchars($item['nama_produk']) . '</td>
            <td>' . htmlspecialchars($item['qty']) . '</td>
            <td>Rp. ' . htmlspecialchars(number_format($item['harga'], 2)) . '</td>
            <td>' . htmlspecialchars(number_format($item['total_harga'], 2)) . '</td>
        </tr>';
    }

    $totalBayarFormatted = htmlspecialchars(number_format($totalBayar, 2));

    $html .= '
        <tr class="table-header">
            <th style="text-align: left;">Total Bayar</th>
            <td></td> 
            <td></td>
            <td>' . $totalBayarFormatted . '</td>
        </tr>
    </table>

    <table class="table-2">
        <tr>
            <td style="width: 30%; font-weight: bold;">Tanggal Transfer</td>
            <td>' . htmlspecialchars($data[0]['tanggal_transfer']) . '</td>
        </tr>
        <tr>
            <td style="width: 30%; font-weight: bold;">Metode Pembayaran</td>
            <td>' . htmlspecialchars($data[0]['metode_pembayaran']) . '</td>
        </tr>
        <tr>
            <td style="width: 30%; font-weight: bold;">Alamat Pengiriman</td>
            <td>' . htmlspecialchars($data[0]['alamat_pengiriman']) . '</td>
        </tr>
        <tr>
            <td style="width: 30%; font-weight: bold;">Nama Penerima</td>
            <td>' . htmlspecialchars($data[0]['nama_penerima']) . '</td>
        </tr>
        <tr>
            <td style="width: 30%; font-weight: bold;">Tanggal Pesanan</td>
            <td>' . htmlspecialchars($data[0]['tanggal_pesanan']) . '</td>
        </tr>
    </table>

    <div class="footer">
        <p>Terima kasih atas kepercayaan Anda berbelanja dengan kami. Kami berharap dapat melayani Anda kembali di masa yang akan datang.</p>
        <div class="promotions">
            <p>Promo Menarik! Dapatkan diskon 10% untuk pembelian berikutnya dengan menggunakan kode promo: <strong>DISKON10</strong></p>
        </div>
    </div>
</body>
</html>';

    // Load HTML ke DOMPDF
    $dompdf->loadHtml($html);

    // (Opsional) Mengatur ukuran kertas dan orientasi
    $dompdf->setPaper('A4', 'portrait');

    // Render PDF
    $dompdf->render();

    // Output PDF ke browser
    $dompdf->stream("nota_pesanan_$id.pdf", array("Attachment" => false));
} else {
    echo "Data nota tidak ditemukan.";
}
?>
