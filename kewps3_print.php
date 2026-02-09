<?php
session_start();
require 'db.php';

// Verify admin access
if (!isset($_SESSION['ID_staf']) || $_SESSION['is_admin'] != 1) {
    die("Akses ditolak. Sila log masuk sebagai admin.");
}

// Get parameters
$no_kod = $_GET['no_kod'] ?? null;
$tarikh_mula = $_GET['tarikh_mula'] ?? null;
$tarikh_akhir = $_GET['tarikh_akhir'] ?? null;

if (!$no_kod || !$tarikh_mula || !$tarikh_akhir) {
    die("Parameter tidak lengkap. Sila kembali dan isi semula borang.");
}

// Get item details
$stmt_barang = $conn->prepare("SELECT no_kod, perihal_stok, unit_pengukuran, harga_seunit, baki_semasa FROM barang WHERE no_kod = ?");
$stmt_barang->bind_param("s", $no_kod);
$stmt_barang->execute();
$barang = $stmt_barang->get_result()->fetch_assoc();
$stmt_barang->close();

if (!$barang) {
    die("Barang tidak dijumpai.");
}

// Get opening balance (last transaction before start date)
$stmt_opening = $conn->prepare("
    SELECT baki_selepas_transaksi, tarikh_transaksi 
    FROM transaksi_stok 
    WHERE no_kod = ? AND DATE(tarikh_transaksi) < ? 
    ORDER BY tarikh_transaksi DESC 
    LIMIT 1
");
$stmt_opening->bind_param("is", $no_kod, $tarikh_mula);
$stmt_opening->execute();
$opening_result = $stmt_opening->get_result();
$opening_balance = 0;

if ($opening_row = $opening_result->fetch_assoc()) {
    $opening_balance = $opening_row['baki_selepas_transaksi'];
}
$stmt_opening->close();

// Get all transactions in the date range
$stmt_transactions = $conn->prepare("
    SELECT
        ts.*,
        s.nama as nama_pegawai,
        j.nama_jabatan as nama_jabatan,
        pelulus.nama as nama_pelulus
    FROM transaksi_stok ts
    LEFT JOIN staf s ON ts.ID_pegawai = s.ID_staf
    LEFT JOIN permohonan p ON ts.ID_rujukan_permohonan = p.ID_permohonan
    LEFT JOIN jabatan j ON p.ID_jabatan = j.ID_jabatan
    LEFT JOIN staf pelulus ON p.ID_pelulus = pelulus.ID_staf
    WHERE ts.no_kod = ?
    AND DATE(ts.tarikh_transaksi) BETWEEN ? AND ?
    ORDER BY ts.tarikh_transaksi ASC
");
$stmt_transactions->bind_param("iss", $no_kod, $tarikh_mula, $tarikh_akhir);
$stmt_transactions->execute();
$transactions = $stmt_transactions->get_result();
$stmt_transactions->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KEW.PS-3_<?php echo htmlspecialchars($barang['perihal_stok']) . '_' . date('d-m-Y', strtotime($tarikh_mula)) . '_hingga_' . date('d-m-Y', strtotime($tarikh_akhir)); ?></title>
    <link rel="icon" type="image/png" href="assets/img/favicon-32.png">
    <style>
        @page {
            size: A4 portrait;
            margin: 0.4in 0.5in;
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
            width: 19cm;
            min-height: 27cm;
            padding: 12px;
            margin: 20px auto;
            background: #FFF;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            font-size: 8.5pt;
            margin-bottom: 3px;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 2px;
        }

        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 2px 0;
            letter-spacing: 2px;
        }

        table.transactions {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            margin-bottom: 0;
        }

        table.transactions th,
        table.transactions td {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: center;
            font-size: 8.5pt;
            line-height: 1.2;
            height: 24px;
        }

        table.transactions th {
            font-weight: bold;
            vertical-align: middle;
            padding: 5px 3px;
            background-color: #f5f5f5;
        }

        table.transactions td {
            vertical-align: middle;
        }

        table.transactions td.left {
            text-align: left;
            padding-left: 5px;
        }

        table.transactions td.right {
            text-align: right;
            padding-right: 5px;
        }

        table.transactions td.date-cell {
            font-size: 7pt;
            white-space: nowrap;
            padding: 2px 1px;
        }

        .opening-balance {
            font-weight: normal;
        }

        .opening-balance td {
            padding: 4px 5px;
        }

        .page-footer {
            margin-top: 2px;
            padding-top: 2px;
            padding-left: 0;
            font-size: 8pt;
            color: #000;
            line-height: 1.2;
            text-align: left;
        }

        .page-footer p {
            margin: 0.5px 0;
            text-align: left;
        }

        .page-footer p:first-child {
            font-style: italic;
            font-weight: normal;
        }

        .page-footer p:not(:first-child) {
            font-style: italic;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        .page-break {
            page-break-before: always;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background-color: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .document-container {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 8px !important;
                width: 100% !important;
                max-width: none !important;
                transform: scale(1) !important;
                transform-origin: top left;
            }

            .transactions {
                width: 100% !important;
                table-layout: fixed;
            }

            .page-break {
                page-break-before: always;
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
    </style>
    <script>
        // Generate filename for print/save
        window.onload = function() {
            const filename = "KEW.PS-3_<?php echo htmlspecialchars($barang['perihal_stok']) . '_' . date('d-m-Y', strtotime($tarikh_mula)) . '_hingga_' . date('d-m-Y', strtotime($tarikh_akhir)); ?>";
            document.title = filename;
        };
    </script>
</head>
<body>
<?php
// Prepare all transactions as array for pagination
$all_transactions = [];
$running_balance = $opening_balance;

if ($transactions->num_rows > 0) {
    while ($txn = $transactions->fetch_assoc()) {
        $all_transactions[] = $txn;
    }
}

$total_transactions = count($all_transactions);
$max_rows_per_page = 28;
$total_pages = ($total_transactions > 0) ? ceil($total_transactions / $max_rows_per_page) : 1;

// Function to render table header
function render_table_header() {
?>
    <thead>
        <tr>
            <th rowspan="2" style="width: 50px;">Tarikh</th>
            <th rowspan="2" style="width: 55px;">No.PK/<br>BTB/<br>BPSS/<br>BPSI/<br>BPIN</th>
            <th rowspan="2" style="width: 90px;">Terima<br>Daripada/<br>Keluar<br>Kepada</th>
            <th colspan="3" style="border-bottom: 1px solid #000;">TERIMAAN</th>
            <th colspan="2" style="border-bottom: 1px solid #000;">KELUARAN</th>
            <th colspan="2" style="border-bottom: 1px solid #000;">BAKI</th>
            <th rowspan="2" style="width: 75px;">Nama<br>Pegawai</th>
        </tr>
        <tr>
            <th style="width: 40px;">Kuantiti</th>
            <th style="width: 45px;">Seunit<br>(RM)</th>
            <th style="width: 50px;">Jumlah<br>(RM)</th>
            <th style="width: 40px;">Kuantiti</th>
            <th style="width: 50px;">Jumlah<br>(RM)</th>
            <th style="width: 40px;">Kuantiti</th>
            <th style="width: 50px;">Jumlah<br>(RM)</th>
        </tr>
    </thead>
<?php
}

// Function to render footer (only on last page)
function render_footer($is_last_page) {
    if (!$is_last_page) return;
?>
    <tfoot>
        <tr style="border: none;">
            <td colspan="11" style="border: none !important; padding: 0;">
                <div class="page-footer">
                    <p style="font-style: italic; font-weight: bold; margin-bottom: 3px;">Nota:</p>
                    <p style="font-style: italic; font-weight: bold;">PK = Pesanan Kerajaan</p>
                    <p style="font-style: italic; font-weight: bold;">BTB = Borang Terimaan Barang-barang</p>
                    <p style="font-style: italic; font-weight: bold;">BPSS = Borang Permohonan Stok (KEW.PS-7)</p>
                    <p style="font-style: italic; font-weight: bold;">BPSI = Borang Permohonan Stok (KEW.PS-8)</p>
                    <p style="font-style: italic; font-weight: bold;">BPIN = Borang Pindahan Stok (KEW.PS-17)</p>
                </div>
            </td>
        </tr>
    </tfoot>
<?php
}

// Loop through pages
for ($page = 1; $page <= $total_pages; $page++):
    $is_first_page = ($page == 1);
    $is_last_page = ($page == $total_pages);

    // Calculate transaction range for this page
    $start_index = ($page - 1) * $max_rows_per_page;
    $end_index = min($start_index + $max_rows_per_page, $total_transactions);
    $transactions_on_page = $end_index - $start_index;
    $empty_rows = $max_rows_per_page - $transactions_on_page;
?>
    <!-- Page <?php echo $page; ?> -->
    <div class="document-container<?php echo $page > 1 ? ' page-break' : ''; ?>">
        <div class="page-header">
            <span>Pekeliling Perbendaharaan Malaysia</span>
            <span>AM 6.3 Lampiran A</span>
        </div>

        <div class="header">
            <h3>BAHAGIAN B</h3>
        </div>

        <div style="text-align: left; margin-bottom: 5px; font-size: 10pt; font-weight: 600;">Transaksi Stok</div>

        <table class="transactions">
            <?php render_table_header(); ?>
            <tbody>
                <?php if ($is_first_page): ?>
                <!-- Opening Balance Row (first page only) -->
                <tr class="opening-balance">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td colspan="5" class="left" style="font-weight: normal; padding-left: 5px;">Baki dibawa ke hadapan................................ <span style="letter-spacing: 2px;"></span></td>
                    <td class="right" style="font-weight: normal;"><?php echo number_format($opening_balance); ?></td>
                    <td class="right" style="font-weight: normal;"><?php echo number_format($opening_balance * $barang['harga_seunit'], 2); ?></td>
                    <td></td>
                </tr>
                <?php
                    $running_balance = $opening_balance;
                endif;
                ?>

                <?php
                // Render transactions for this page
                for ($i = $start_index; $i < $end_index; $i++):
                    $txn = $all_transactions[$i];
                    $is_in = ($txn['jenis_transaksi'] == 'Masuk');
                    $kuantiti = $txn['kuantiti'];
                    $harga = $barang['harga_seunit'];
                    $jumlah = $kuantiti * $harga;
                    $running_balance = $txn['baki_selepas_transaksi'];
                    $balance_value = $running_balance * $harga;
                ?>
                <tr>
                    <td class="date-cell"><?php echo date('d/m/Y', strtotime($txn['tarikh_transaksi'])); ?></td>
                    <td></td>
                    <td class="left"><?php echo htmlspecialchars($txn['nama_jabatan'] ?? $txn['terima_dari_keluar_kepada'] ?? '-'); ?></td>

                    <!-- TERIMAAN (Received) -->
                    <td class="right"><?php echo $is_in ? number_format($kuantiti) : ''; ?></td>
                    <td class="right"><?php echo $is_in ? number_format($harga, 2) : ''; ?></td>
                    <td class="right"><?php echo $is_in ? number_format($jumlah, 2) : ''; ?></td>

                    <!-- KELUARAN (Issued) -->
                    <td class="right"><?php echo !$is_in ? number_format($kuantiti) : ''; ?></td>
                    <td class="right"><?php echo !$is_in ? number_format($jumlah, 2) : ''; ?></td>

                    <!-- BAKI (Balance) -->
                    <td class="right"><?php echo number_format($running_balance); ?></td>
                    <td class="right"><?php echo number_format($balance_value, 2); ?></td>

                    <td class="left" style="font-size: 7.5pt;">
                        <?php echo htmlspecialchars($txn['nama_pelulus'] ?? $txn['nama_pegawai'] ?? '-'); ?>
                    </td>
                </tr>
                <?php endfor; ?>

                <?php
                // Fill remaining rows with empty cells
                for ($i = 0; $i < $empty_rows; $i++):
                ?>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <?php endfor; ?>
            </tbody>
            <?php render_footer($is_last_page); ?>
        </table>
    </div>
<?php endfor; ?>

    <div class="print-controls no-print">
        <button onclick="window.print()" class="btn btn-primary">Cetak Dokumen</button>
        <button onclick="window.history.back()" class="btn btn-secondary">Kembali</button>
    </div>
</body>
</html>