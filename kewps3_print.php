<?php
// FILE: kewps3_print.php
session_start();
require 'db.php';

// Verify admin access (support both old and new auth system)
$is_admin = (isset($_SESSION['peranan']) && $_SESSION['peranan'] == 'Admin') ||
            (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) ||
            (isset($_SESSION['is_superadmin']) && $_SESSION['is_superadmin'] == 1);

if (!isset($_SESSION['ID_staf']) || !$is_admin) {
    die("Akses ditolak. Sila log masuk sebagai pentadbir.");
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
    <title>KEW.PS-3 Bahagian B - <?php echo htmlspecialchars($barang['perihal_stok']); ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
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
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            font-size: 9pt;
            margin-bottom: 10px;
        }
        
        .header h1 {
            font-size: 12pt;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .item-info {
            margin: 15px 0;
            font-size: 10pt;
        }
        
        .item-info table {
            width: 100%;
            margin-bottom: 10px;
        }
        
        .item-info td {
            padding: 3px 0;
        }
        
        .item-info td:first-child {
            width: 150px;
            font-weight: bold;
        }
        
        table.transactions {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        table.transactions th,
        table.transactions td {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: center;
            font-size: 9pt;
        }
        
        table.transactions th {
            background-color: #f0f0f0;
            font-weight: bold;
            vertical-align: middle;
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
            background-color: #fffacd;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 20px;
            font-size: 8pt;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        
        .footer p {
            margin: 2px 0;
        }
        
        @media print {
            .no-print {
                display: none;
            }
            
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        
        .print-button {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        
        .print-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">
        🖨️ Cetak Laporan
    </button>

    <div class="header">
        <div class="header-top">
            <span>Pekeiling Perbendaharaan Malaysia</span>
            <span>AM 6.3 Lampiran A</span>
        </div>
        <h1>BAHAGIAN B</h1>
        <div style="margin-top: 5px; font-size: 11pt;">Transaksi Stok</div>
    </div>

    <div class="item-info">
        <table>
            <tr>
                <td>Nama Barang:</td>
                <td><?php echo htmlspecialchars($barang['perihal_stok']); ?></td>
            </tr>
            <tr>
                <td>No. Kod:</td>
                <td><?php echo $barang['no_kod']; ?></td>
            </tr>
            <tr>
                <td>Unit Pengukuran:</td>
                <td><?php echo htmlspecialchars($barang['unit_pengukuran'] ?? 'Unit'); ?></td>
            </tr>
            <tr>
                <td>Harga Seunit:</td>
                <td>RM <?php echo number_format($barang['harga_seunit'], 2); ?></td>
            </tr>
            <tr>
                <td>Tempoh Laporan:</td>
                <td><?php echo date('d/m/Y', strtotime($tarikh_mula)); ?> hingga <?php echo date('d/m/Y', strtotime($tarikh_akhir)); ?></td>
            </tr>
        </table>
    </div>

    <table class="transactions">
        <thead>
            <tr>
                <th rowspan="2" style="width: 60px;">Tarikh</th>
                <th rowspan="2" style="width: 80px;">No PK/<br>BTB/<br>BPSS/<br>BPSI/<br>BPIN</th>
                <th rowspan="2" style="width: 100px;">Terima<br>Daripada/<br>Keluar<br>Kepada</th>
                <th colspan="3">TERIMAAN</th>
                <th colspan="2">KELUARAN</th>
                <th colspan="2">BAKI</th>
                <th rowspan="2" style="width: 80px;">Nama<br>Pegawai</th>
            </tr>
            <tr>
                <th style="width: 40px;">Kuantiti</th>
                <th style="width: 50px;">Seunit<br>(RM)</th>
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
                <td colspan="8" class="left">Baki dibawa ke hadapan</td>
                <td class="right"><?php echo number_format($opening_balance); ?></td>
                <td class="right">
                    <?php echo number_format($opening_balance * $barang['harga_seunit'], 2); ?>
                </td>
                <td></td>
            </tr>

            <?php 
            $running_balance = $opening_balance;
            if ($transactions->num_rows > 0):
                while ($txn = $transactions->fetch_assoc()): 
                    $is_in = ($txn['jenis_transaksi'] == 'Masuk');
                    $kuantiti = $txn['kuantiti'];
                    $harga = $barang['harga_seunit'];
                    $jumlah = $kuantiti * $harga;
                    
                    // Use actual balance from database
                    $running_balance = $txn['baki_selepas_transaksi'];
                    $balance_value = $running_balance * $harga;
            ?>
            <tr>
                <td><?php echo date('d/m/Y', strtotime($txn['tarikh_transaksi'])); ?></td>
                <td></td> <!-- Document number left blank for security -->
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
                
                <td class="left" style="font-size: 8pt;">
                    <?php echo htmlspecialchars($txn['nama_pegawai'] ?? '-'); ?>
                </td>
            </tr>
            <?php 
                endwhile;
            else:
            ?>
            <tr>
                <td colspan="11" class="left" style="padding: 20px; text-align: center; color: #666;">
                    Tiada transaksi dalam tempoh yang dipilih
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Nota:</strong></p>
        <p>PK = Pesanan Kerajaan</p>
        <p>BTB = Borang Terimaan Barang-barang</p>
        <p>BPSS = Borang Permohonan Stok (KEW.PS-7)</p>
        <p>BPSI = Borang Permohonan Stok (KEW.PS-8)</p>
        <p>BPIN = Borang Pindahan Stok (KEW.PS-17)</p>
        <p style="margin-top: 10px; font-style: italic; color: #666;">
            Laporan dijana pada: <?php echo date('d/m/Y H:i:s'); ?>
        </p>
    </div>

    <script>
        // Auto print on load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>