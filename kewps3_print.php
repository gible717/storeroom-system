<?php
// FILE: kewps3_print.php
session_start();
require 'db.php';

// Verify admin access (support both old and new auth system)
$is_admin = (isset($_SESSION['peranan']) && $_SESSION['peranan'] == 'Admin') ||
            (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) ||
            (isset($_SESSION['is_superadmin']) && $_SESSION['is_superadmin'] == 1);

if (!isset($_SESSION['ID_staf']) || !$is_admin) {
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
$stmt_barang = $conn->prepare("SELECT * FROM barang WHERE no_kod = ?");
$stmt_barang->bind_param("i", $no_kod);
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
        s.nama as nama_pegawai
    FROM transaksi_stok ts
    LEFT JOIN staf s ON ts.ID_pegawai = s.ID_staf
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
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 15mm 10mm 15mm;
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
            max-width: 750px;
            margin: 0 auto;
            padding: 10px;
            background-color: #e9ecef;
        }

        .document-container {
            background-color: #fff;
            padding: 15px 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            font-size: 8.5pt;
            margin-bottom: 8px;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 5px;
        }

        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
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
            padding: 6px 4px;
            text-align: center;
            font-size: 9pt;
            line-height: 1.3;
            height: 28px;
        }

        table.transactions th {
            font-weight: bold;
            vertical-align: middle;
            padding: 8px 4px;
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

        .opening-balance {
            font-weight: normal;
        }

        .opening-balance td {
            padding: 6px 6px;
        }

        .page-footer {
            margin-top: 8px;
            padding-top: 8px;
            padding-left: 0;
            font-size: 8.5pt;
            color: #000;
            line-height: 1.4;
            text-align: left;
        }

        .page-footer p {
            margin: 2px 0;
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

        @media print {
            .no-print {
                display: none;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                max-width: 100%;
                background-color: #fff;
                padding: 0;
                margin: 0;
            }

            .document-container {
                box-shadow: none;
                padding: 10px 15px;
                margin: 0;
            }

            .page-header {
                margin-bottom: 8px;
            }

            .header {
                margin-bottom: 5px;
            }

            table.transactions th,
            table.transactions td {
                padding: 6px 4px;
                font-size: 9pt;
                height: 28px;
            }

            table.transactions th {
                padding: 8px 4px;
            }

            .page-footer {
                margin-top: 8px;
                padding-top: 8px;
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
    <!-- Page Header (repeats on each page) -->
    <div class="document-container">
    <div class="page-header">
        <span>Pekeiling Perbendaharaan Malaysia</span>
        <span>AM 6.3 Lampiran A</span>
    </div>

    <!-- Main Header (first page only) -->
    <div class="header">
        <h3>BAHAGIAN B</h3>
    </div>

    <div style="text-align: left; margin-bottom: 8px; font-size: 10pt; font-weight: 600;">Transaksi Stok</div>

    <table class="transactions">
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
        <tbody>
            <!-- Opening Balance Row -->
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
            $row_count = 0;
            $max_rows_per_page = 25; // Approximate number of rows that fit on one page

            if ($transactions->num_rows > 0):
                while ($txn = $transactions->fetch_assoc()):
                    $is_in = ($txn['jenis_transaksi'] == 'Masuk');
                    $kuantiti = $txn['kuantiti'];
                    $harga = $barang['harga_seunit'];
                    $jumlah = $kuantiti * $harga;

                    // Use actual balance from database
                    $running_balance = $txn['baki_selepas_transaksi'];
                    $balance_value = $running_balance * $harga;
                    $row_count++;
            ?>
            <tr>
                <td><?php echo date('d/m/Y', strtotime($txn['tarikh_transaksi'])); ?></td>
                <td></td>
                <td class="left"><?php echo htmlspecialchars($txn['terima_dari_keluar_kepada'] ?? '-'); ?></td>

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
                    <?php echo htmlspecialchars($txn['nama_pegawai'] ?? '-'); ?>
                </td>
            </tr>
            <?php
                endwhile;
            endif;

            // Fill remaining rows with empty cells to match official format
            for ($i = $row_count; $i < $max_rows_per_page; $i++):
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
        <tfoot>
            <tr>
                <td colspan="11" style="border: none; padding: 0;">
                    <div class="page-footer">
                        <p style="font-style: italic; margin-bottom: 3px;">Nota:</p>
                        <p style="font-style: italic;">PK = Pesanan Kerajaan</p>
                        <p style="font-style: italic;">BTB = Borang Terimaan Barang-barang</p>
                        <p style="font-style: italic;">BPSS = Borang Permohonan Stok (KEW.PS-7)</p>
                        <p style="font-style: italic;">BPSI = Borang Permohonan Stok (KEW.PS-8)</p>
                        <p style="font-style: italic;">BPIN = Borang Pindahan Stok (KEW.PS-17)</p>
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>
    </div>

    <div class="print-controls no-print">
        <button onclick="window.print()" class="btn btn-primary">Cetak Dokumen</button>
        <button onclick="window.close()" class="btn btn-secondary">Kembali</button>
    </div>
</body>
</html>