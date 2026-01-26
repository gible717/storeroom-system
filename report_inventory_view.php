<?php
// report_inventory_view.php - Printer-friendly inventory report
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['ID_staf'])) {
    header("Location: login.php");
    exit;
}

// Get month filter (default to current month)
$selected_month = $_GET['month'] ?? date('Y-m');
list($year, $month) = explode('-', $selected_month);

// Calculate date ranges
$month_start = "$selected_month-01";
$month_end = date('Y-m-t', strtotime($month_start));

// Get category filter
$kategori_filter = $_GET['kategori'] ?? '';

// Build WHERE clause
$where_clause = "";
$params = [];
$types = "";

if ($kategori_filter !== '') {
    $where_clause = "WHERE b.kategori = ?";
    $params[] = $kategori_filter;
    $types = "s";
}

// Get all barang (inventory items)
$sql = "SELECT
            b.no_kod,
            b.perihal_stok AS nama_produk,
            b.kategori,
            b.baki_semasa AS stok_semasa,
            b.harga_seunit AS harga_unit,
            (b.baki_semasa * b.harga_seunit) AS nilai_semasa
        FROM barang b
        $where_clause
        ORDER BY b.no_kod ASC";

$stmt = $conn->prepare($sql);
if ($kategori_filter !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$inventory = $stmt->get_result();

// Calculate totals and movements
$total_harga_seunit = 0;
$total_nilai_semasa = 0;
$total_stok_semasa = 0;

// Store results in array with stock movements
$products = [];
while ($row = $inventory->fetch_assoc()) {
    $no_kod = $row['no_kod'];

    // Calculate stock movements from transaksi_stok table
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
    $total_harga_seunit += $row['harga_unit'];
    $total_nilai_semasa += $row['nilai_semasa'];
    $total_stok_semasa += $row['stok_semasa'];
}

// Month name in Malay
$months_ms = ['Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun',
              'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'];
$month_name = $months_ms[(int)$month - 1] . ' ' . $year;

// Get all categories for filter dropdown from barang table
$kategori_sql = "SELECT DISTINCT kategori AS nama_kategori FROM barang WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC";
$kategori_result = $conn->query($kategori_sql);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Inventori - <?php echo $month_name; ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0.5in;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
            background-color: #F8F8F8;
        }

        .document-container {
            width: 21cm;
            min-height: 29.7cm;
            padding: 1cm;
            margin: 20px auto;
            background: #FFF;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            page-break-after: always;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background-color: #fff !important;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .document-container {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 15px;
                width: 100% !important;
                max-width: none !important;
                transform: scale(1) !important;
                transform-origin: top left;
            }

            .report-header {
                margin-bottom: 15px;
                padding-bottom: 10px;
            }

            table {
                font-size: 9pt;
                page-break-inside: auto;
                width: 100% !important;
                table-layout: fixed;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-row-group;
            }

            table th,
            table td {
                padding: 5px !important;
            }
        }

        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            border: none;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            margin-left: 10px;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .print-controls {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
        }

        .filter-controls {
            text-align: center;
            margin: 10px 0 20px 0;
            padding: 10px;
        }

        .filter-controls select {
            padding: 8px 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
            margin-left: 10px;
        }

        .report-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }

        .report-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .report-info {
            font-size: 12px;
            color: #555;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9pt;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        thead th {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: center;
            font-size: 9pt;
        }

        tfoot th {
            background-color: #d1d5db;
            font-weight: bold;
            font-size: 10pt;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <!-- Filter Controls (outside container) -->
    <div class="filter-controls no-print">
        <form method="GET" style="display: inline-block;">
            <input type="hidden" name="month" value="<?php echo htmlspecialchars($selected_month); ?>">
            <label><strong>Kategori:</strong></label>
            <select name="kategori" onchange="this.form.submit()">
                <option value="Semua" <?php if ($kategori_filter == 'Semua') echo 'selected'; ?>>Semua</option>
                <?php
                $kategori_result->data_seek(0); // Reset pointer
                while($k = $kategori_result->fetch_assoc()):
                ?>
                    <option value="<?php echo htmlspecialchars($k['nama_kategori']); ?>"
                            <?php if ($kategori_filter == $k['nama_kategori']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($k['nama_kategori']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>
    </div>

    <!-- Document Container -->
    <div class="document-container">
        <!-- Report Header -->
        <div class="report-header">
            <div class="report-title">LAPORAN INVENTORI</div>
            <div class="report-info">
                <?php if ($kategori_filter !== 'Semua'): ?>
                    <strong>Kategori:</strong> <?php echo htmlspecialchars($kategori_filter); ?>
                <?php else: ?>
                    <strong>Semua Kategori</strong>
                <?php endif; ?>
            </div>
            <div class="report-info">
                <strong>Tarikh Laporan:</strong> <?php echo date('d/m/Y'); ?>
            </div>
        </div>

        <!-- Inventory Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">Bil.</th>
                    <th style="width: 80px;">ID Produk</th>
                    <th>Nama Produk</th>
                    <th style="width: 100px;">Kategori</th>
                    <th style="width: 80px;">Stok Semasa<br>(Unit)</th>
                    <th style="width: 100px;">Harga Seunit<br>(RM)</th>
                    <th style="width: 100px;">Nilai Semasa<br>(RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php
                    $bil = 1;
                    foreach ($products as $row):
                    ?>
                        <tr>
                            <td class="text-center"><?php echo $bil++; ?></td>
                            <td><?php echo htmlspecialchars($row['no_kod']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['kategori'] ?? '-'); ?></td>
                            <td class="text-center"><?php echo number_format($row['stok_semasa']); ?></td>
                            <td class="text-end"><?php echo number_format($row['harga_unit'], 2); ?></td>
                            <td class="text-end"><?php echo number_format($row['nilai_semasa'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center" style="color: #999;">
                            Tiada produk ditemui untuk kategori ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if (count($products) > 0): ?>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end">JUMLAH KESELURUHAN (RM)</th>
                    <th class="text-center"><?php echo number_format($total_stok_semasa); ?></th>
                    <th class="text-end"><?php echo number_format($total_harga_seunit, 2); ?></th>
                    <th class="text-end"><?php echo number_format($total_nilai_semasa, 2); ?></th>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>

    <!-- Print Controls (outside container) -->
    <div class="print-controls no-print">
        <button onclick="window.print()" class="btn btn-primary">Cetak Dokumen</button>
        <a href="report_inventory.php" class="btn btn-secondary">Kembali</a>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
