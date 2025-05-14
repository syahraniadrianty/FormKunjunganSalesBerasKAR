<?php
require_once 'vendor/autoload.php';


error_reporting(E_ALL);
ini_set('display_errors', 1);

use Google\Client;
use Google\Service\Drive;
use Google\Service\Sheets;
use Google\Service\Drive\DriveFile;

date_default_timezone_set("Asia/Jakarta");

// Konfigurasi
$credentialsPath = 'kunjungansalesberaskar-ae464a920fa5.json'; // Ganti dengan path file credentials
$folderId = '1-uyQfCcPZx_rbxcy6UayQwWCIzB-bkMS'; // Folder ID dari link Google Drive kamu
$spreadsheetId = '1fJGQCtwLnvcEE4U2LEhX3Rzq6l4gy7wIygWP3b-t1Fc'; // ID Google Sheets
$range = 'HasilKunjunganSalesBerasKAR'; // Nama sheet

// Inisialisasi client
$client = new Client();
$client->setAuthConfig($credentialsPath);
$client->setScopes([
    Sheets::SPREADSHEETS,
    Drive::DRIVE
]);
$client->setAccessType('offline');

$sheetService = new Sheets($client);
$driveService = new Drive($client);

// Ambil data dari form
$timestamp = date("Y-m-d H:i:s");
$nama_sales         = $_POST['nama_sales'] ?? '';
$tanggal_kunjungan  = $_POST['tanggal_kunjungan'] ?? '';
$kategori_toko      = $_POST['kategori_toko'] ?? '';
$nama_toko          = $_POST['nama_toko'] ?? '';
$status_toko        = $_POST['status_toko'] ?? '';
$provinsi           = $_POST['provinsi'] ?? '';
$kota_kabupaten     = $_POST['kota_kabupaten'] ?? '';
$kecamatan          = $_POST['kecamatan'] ?? '';
$kelurahan          = $_POST['kelurahan'] ?? '';
$nama_pic           = $_POST['nama_pic'] ?? '';
$no_pic             = $_POST['no_pic'] ?? '';
$kegiatan           = $_POST['kegiatan'] ?? '';
$deskripsi_kegiatan = $_POST['deskripsi_kegiatan'] ?? '';
$kgberas_penjualanBerasToko = $_POST['kgberas_penjualanBerasToko'] ?? 0;
$produk_pesaing     = $_POST['produk_pesaing'] ?? '';
$kg_produkpesaing   = $_POST['kg_produkpesaing'] ?? 0;
$harga_produkpesaing = $_POST['harga_produkpesaing'] ?? 0;

$rincianPO_UnGlu5kg_pack        = $_POST['rincianPO_UnGlu5kg_pack'] ?? 0;
$rincianPO_Segowangi5kg_pack    = $_POST['rincianPO_Segowangi5kg_pack'] ?? 0;
$rincianPO_Segowangi2_5kg_pack  = $_POST['rincianPO_Segowangi2_5kg_pack'] ?? 0;
$rincianPO_CapSego10kg_pack     = $_POST['rincianPO_CapSego10kg_pack'] ?? 0;
$rincianPO_Medium25kg_pack      = $_POST['rincianPO_Medium25kg_pack'] ?? 0;
$rincianPO_Medium50kg_pack      = $_POST['rincianPO_Medium50kg_pack'] ?? 0;

$lokasi_toko = $_POST['lokasi_toko'] ?? '';

// ✅ Upload foto ke Google Drive dan dapatkan link public
$publicUrl = '';
if (!empty($_FILES['foto_toko']['name'])) {
    $file_name = $_FILES['foto_toko']['name'];
    $tmp_file = $_FILES['foto_toko']['tmp_name'];
    $mime_type = mime_content_type($tmp_file);

    $file = new DriveFile([
        'name' => time() . '_' . $file_name,
        'parents' => [$folderId]
    ]);

    $uploadedFile = $driveService->files->create($file, [
        'data' => file_get_contents($tmp_file),
        'mimeType' => $mime_type,
        'uploadType' => 'multipart',
        'fields' => 'id'
    ]);

    $fileId = $uploadedFile->id;

    // 🔓 Ubah permission file jadi public
    $driveService->permissions->create($fileId, new Drive\Permission([
        'type' => 'anyone',
        'role' => 'reader',
    ]));

    $publicUrl = "https://drive.google.com/uc?id=$fileId";
}

// ✅ Siapkan data untuk Google Sheets
$values = [[
    $timestamp,
    $nama_sales,
    $tanggal_kunjungan,
    $kategori_toko,
    $nama_toko,
    $status_toko,
    $provinsi,
    $kota_kabupaten,
    $kecamatan,
    $kelurahan,
    $nama_pic,
    $no_pic,
    $kegiatan,
    $deskripsi_kegiatan,
    (float)$kgberas_penjualanBerasToko,
    $produk_pesaing,
    (float)$kg_produkpesaing,
    (float)$harga_produkpesaing,
    (int)$rincianPO_UnGlu5kg_pack,
    (int)$rincianPO_Segowangi5kg_pack,
    (int)$rincianPO_Segowangi2_5kg_pack,
    (int)$rincianPO_CapSego10kg_pack,
    (int)$rincianPO_Medium25kg_pack,
    (int)$rincianPO_Medium50kg_pack,
    $publicUrl,
    $lokasi_toko
]];

// ✅ Simpan ke Google Sheets
$body = new Sheets\ValueRange(['values' => $values]);
$params = ['valueInputOption' => 'RAW'];

try {
    $sheetService->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
    echo "✅ Data berhasil dikirim ke Google Sheets!";
} catch (Exception $e) {
    echo "❌ Error saat kirim data: " . $e->getMessage();
}
?>