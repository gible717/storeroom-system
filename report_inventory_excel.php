<?php
// report_inventory_excel.php - Export inventory report to CSV (opens in Excel)

require_once 'db.php';
require_once 'admin_auth_check.php';

// Get month filter (default to current month)
$selected_month = $_GET['month'] ?? date('Y-m');
list($year, $month) = explode('-', $selected_month);

// Get category filter
$selected_kategori = $_GET['kategori'] ?? '';

// Calculate date ranges
$month_start = "$selected_month-01";
$month_end = date('Y-m-t', strtotime($month_start));

// Build WHERE clause for category filter
$kategori_condition = "";
if ($selected_kategori !== '') {
    $kategori_condition = "AND b.kategori = '" . $conn->real_escape_string($selected_kategori) . "'";
}

// Get all items (including those without transactions in the selected month)
$sql = "SELECT DISTINCT
    b.no_kod,
    b.perihal_stok AS nama_produk,
    b.baki_semasa AS stok_semasa,
    b.harga_seunit AS harga_unit,
    b.kategori AS nama_kategori,
    (b.baki_semasa * b.harga_seunit) AS jumlah_harga
FROM barang b
WHERE 1=1
$kategori_condition
ORDER BY b.no_kod ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

// Calculate stock movements and prepare data
$products = [];
$total_items = 0;
$total_stock = 0;
$total_value = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $no_kod = $row['no_kod'];

        // Calculate stock movements
        $stmt_movement = $conn->prepare("
            SELECT
                SUM(CASE WHEN jenis_transaksi = 'Masuk' THEN kuantiti ELSE 0 END) AS masuk,
                SUM(CASE WHEN jenis_transaksi = 'Keluar' THEN kuantiti ELSE 0 END) AS keluar
            FROM transaksi_stok
            WHERE no_kod = ?
            AND DATE(tarikh_transaksi) BETWEEN ? AND ?
        ");
        $stmt_movement->bind_param("sss", $no_kod, $month_start, $month_end);
        $stmt_movement->execute();
        $movement = $stmt_movement->get_result()->fetch_assoc();

        $row['masuk'] = $movement['masuk'] ?? 0;
        $row['keluar'] = $movement['keluar'] ?? 0;
        $row['baki_bulan_lepas'] = $row['stok_semasa'] + $row['keluar'] - $row['masuk'];

        $products[] = $row;

        $total_items++;
        $total_stock += $row['stok_semasa'];
        $total_value += $row['jumlah_harga'];
    }
}

// Month name in Malay
$months_ms = ['Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun',
              'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'];
$selected_month_name = $months_ms[(int)$month - 1] . ' ' . $year;

// Set headers for CSV download (Excel will open this)
$filename = "Laporan_Inventori_" . $selected_month . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel to recognize special characters
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header Section
fputcsv($output, ['MAJLIS PERBANDARAN KANGAR']);
fputcsv($output, ['LAPORAN INVENTORI BILIK STOR']);
fputcsv($output, ['Bulan: ' . $selected_month_name]);
if ($selected_kategori !== '') {
    fputcsv($output, ['Kategori: ' . $selected_kategori]);
}
fputcsv($output, ['Tarikh Cetak: ' . date('d/m/Y H:i:s')]);
fputcsv($output, []); // Empty row

// Summary Section
fputcsv($output, ['RINGKASAN']);
fputcsv($output, ['Jumlah Item', $total_items]);
fputcsv($output, ['Jumlah Stok', $total_stock . ' Unit']);
fputcsv($output, ['Jumlah Nilai', 'RM ' . number_format($total_value, 2)]);
fputcsv($output, []); // Empty row

// Table Headers
fputcsv($output, [
    'Bil.',
    'No. Kod',
    'Nama Item',
    'Kategori',
    'Harga Unit (RM)',
    'Baki Bln Lepas',
    'Masuk',
    'Keluar',
    'Baki Semasa',
    'Jumlah (RM)'
]);

// Table Data
if (count($products) > 0) {
    $bil = 1;
    $grand_total = 0;

    foreach ($products as $product) {
        $grand_total += $product['jumlah_harga'];

        fputcsv($output, [
            $bil++,
            $product['no_kod'],
            $product['nama_produk'],
            $product['nama_kategori'] ?? '-',
            number_format($product['harga_unit'], 2),
            number_format($product['baki_bulan_lepas']),
            number_format($product['masuk']),
            number_format($product['keluar']),
            number_format($product['stok_semasa']),
            number_format($product['jumlah_harga'], 2)
        ]);
    }

    // Total Row
    fputcsv($output, [
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        'JUMLAH KESELURUHAN:',
        'RM ' . number_format($grand_total, 2)
    ]);
} else {
    fputcsv($output, ['Tiada dalam rekod untuk ' . $selected_month_name]);
}

// Footer
fputcsv($output, []); // Empty row
fputcsv($output, ['Laporan dijana secara automatik oleh Sistem Pengurusan Bilik Stor dan Inventori']);
fputcsv($output, ['© ' . date('Y') . ' Majlis Perbandaran Kangar, Perlis. Hak Cipta Terpelihara.']);

fclose($output);
$conn->close();
exit;
?>
