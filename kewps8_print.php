<?php
require 'auth_check.php';

// Get request ID from URL
$id_permohonan = $_GET['id'] ?? null;
if (!$id_permohonan) {
    die("ID Permohonan tidak sah.");
}

// 2. Fetch Request Header
// Use COALESCE: prefer jawatan from form, fallback to profile, or NULL if both empty
$stmt_header = $conn->prepare("SELECT
                                p.ID_permohonan, p.tarikh_mohon, p.tarikh_lulus,
                                p.nama_pemohon,
                                COALESCE(NULLIF(p.jawatan_pemohon, ''), pemohon.jawatan) AS jawatan_pemohon,
                                j.nama_jabatan,
                                pelulus.nama AS nama_pelulus
                            FROM permohonan p
                            JOIN staf pemohon ON p.ID_pemohon = pemohon.ID_staf
                            LEFT JOIN jabatan j ON p.ID_jabatan = j.ID_jabatan
                            LEFT JOIN staf pelulus ON p.ID_pelulus = pelulus.ID_staf
                            WHERE p.ID_permohonan = ?");
$stmt_header->bind_param("i", $id_permohonan);
$stmt_header->execute();
$header = $stmt_header->get_result()->fetch_assoc();
$stmt_header->close();

if (!$header) {
    die("Permohonan tidak dijumpai.");
}

// 3. Fetch Request Items
$stmt_items = $conn->prepare("SELECT
                                pb.no_kod,
                                b.perihal_stok,
                                pb.kuantiti_mohon,
                                pb.kuantiti_lulus,
                                (t.baki_selepas_transaksi + t.kuantiti) AS baki_sedia_ada,
                                p.catatan AS catatan_pemohon
                            FROM permohonan_barang pb
                            JOIN barang b ON pb.no_kod = b.no_kod
                            JOIN permohonan p ON pb.ID_permohonan = p.ID_permohonan
                            LEFT JOIN transaksi_stok t ON pb.ID_permohonan = t.ID_rujukan_permohonan AND pb.no_kod = t.no_kod
                            WHERE pb.ID_permohonan = ?
                            ORDER BY pb.ID_permohonan_barang ASC");
$stmt_items->bind_param("i", $id_permohonan);
$stmt_items->execute();
$items = $stmt_items->get_result();
$stmt_items->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KEW.PS-8 (ID: <?php echo $id_permohonan; ?>)</title>
    <link rel="icon" type="image/png" href="assets/img/favicon-32.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        @page {
            size: A4 landscape;
            margin: 0.5in;
        }
        body {
            font-family: Arial, sans-serif;
            color: #000;
            background-color: #F8F8F8; 
        }
        .page-container {
            width: 27.7cm; 
            min-height: 19cm; 
            padding: 15px;
            margin: 20px auto;
            background: #FFF;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative; /* Needed for footer positioning */
        }
        
        strong { font-weight: bold; }

        .text-header-gray {
            color: #555;
            font-weight: normal; 
        }
        .text-gray {
            color: #555;
            font-weight: normal;
        }

        /* 1 & 3. UNIFIED TABLE: No bottom margin */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px; 
            /* margin-bottom: 10px; <-- REMOVED this gap */
        }
        .report-table th, .report-table td {
            border: 2px solid #000;
            padding: 3px;
            vertical-align: top;
            height: 30px; 
            text-align: left;
        }
        /* Bold borders between main sections */
        .report-table thead th:nth-child(4),
        .report-table tbody td:nth-child(4),
        .report-table tfoot td:nth-child(4) {
            border-left: 4px solid #000 !important;  /* Bold border after Permohonan */
        }
        .report-table thead th:nth-child(7),
        .report-table tbody td:nth-child(7),
        .report-table tfoot td:nth-child(7) {
            border-left: 4px solid #000 !important;  /* Bold border after Pegawai Pelulus */
        }
        .report-table .center { text-align: center; }

        .report-table th.header-main {
            background-color: #E0E0E0; 
            color: #000;
            font-size: 11px;
            font-weight: normal; 
            text-align: center;
            vertical-align: middle;
        }
        .report-table th.header-sub {
            background-color: #E0E0E0; 
            color: #000;
            font-weight: normal; 
            text-align: center;
            vertical-align: middle;
        }
        
        /* 1 & 3. UNIFIED TABLE: Styling for the new signature footer row */
        .report-table tfoot td {
            height: 120px;
            vertical-align: top;
            padding: 8px;
            font-size: 10px;
        }

        .header-info {
            font-size: 12px;
            font-weight: bold;
        }
        .main-title {
            font-size: 14px;
            text-align: center;
            margin-bottom: 15px;
            margin-top: 10px;
        }

        .signature-space {
            height: 40px; 
            border-bottom: 1px dotted #000;
            margin-top: 10px;
        }
        .footer-note {
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            padding-top: 5px; /* Give it space from the table */
            margin-top: 5px; 
        }

        @media print {
            .no-print { display: none; }
            body {
                background-color: #FFF;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .page-container {
                margin: 0;
                padding: 10px;
                box-shadow: none;
                width: 100% !important;
                max-width: none !important;
                min-height: 0;
                transform: scale(1) !important;
                transform-origin: top left;
            }
            .report-table {
                width: 100% !important;
                table-layout: fixed;
            }
        }
    </style>
</head>
<body>

    <div class="page-container">

        <div class="row header-info">
    <div class="col-6">
        <span class="text-header-gray">Pekeliling Perbendaharaan Malaysia</span>
    </div>
    <div class="col-6">
        <div style="text-align: right; padding-right: 80px;">
            <span class="text-header-gray">AM 6.5 Lampiran B</span>
        </div>
        <div style="text-align: right; padding-right: 80px; margin-top: 10px;">
            <strong style="font-size: 1.1em;">KEW.PS-8</strong>
        </div>
        <div style="text-align: left; padding-left: 288px; margin-top: 3px;">
            <span style="font-weight: normal;">No. BPSI: </span>
        </div>
    </div>
</div>
        <div class="main-title" style="margin-bottom: 10px; margin-top: 5px;">
            <strong>BORANG PERMOHONAN STOK<br>(INDIVIDU KEPADA STOR)</strong>
        </div>

        <div style="padding-left: 40px; margin-bottom: 0px; font-size: 11px; font-weight: normal;">
            Jabatan / Unit: <?php echo htmlspecialchars($header['nama_jabatan'] ?? '-'); ?>
        </div>

        <table class="report-table">
            <thead class="header-row">
                
    <tr class="header-row-main">
        <th colspan="3" class="header-main"><strong>Permohonan</strong></th>
        <th colspan="3" class="header-main" style="border-left: 4px solid #000;"><strong>Pegawai Pelulus</strong></th>
    <th colspan="2" class="header-main" style="border-left: 4px solid #000;"><strong>Perakuan Penerimaan</strong></th>

    <tr class="header-row-sub">
    <th class="header-sub" style="width: 6%;"><strong>No. Kod</strong></th>
    <th class="header-sub" style="width: 17%;"><strong>Perihal Stok</strong></th>
    <th class="header-sub center" style="width: 10%;"><strong>Kuantiti<br>Dimohon</strong></th>
    <th class="header-sub center" style="width: 8%; border-left: 5px solid #000;"><strong>Baki Sedia<br>Ada</strong></th>
    <th class="header-sub center" style="width: 10%;"><strong>Kuantiti<br>Diluluskan</strong></th>
    <th class="header-sub" style="width: 15%;"><strong>Catatan</strong></th>
    <th class="header-sub center" style="width: 11%; border-left: 5px solid #000;"><strong>Kuantiti<br>Diterima</strong></th>
    <th class="header-sub" style="width: 23%;"><strong>Catatan</strong></th>
</tr> 
</thead>
            <tbody>
    <?php 
    $item_count = 0;
    while($item = $items->fetch_assoc()): 
        $item_count++;
    ?>
    <tr>
    <td class="center"><?php echo htmlspecialchars($item['no_kod']); ?></td>
    <td><?php echo htmlspecialchars($item['perihal_stok']); ?></td>
    <td class="center"><?php echo htmlspecialchars($item['kuantiti_mohon']); ?></td>
    <td class="center"><?php echo htmlspecialchars($item['baki_sedia_ada'] ?? '-'); ?></td>
    <td class="center" style="color: red; font-weight: bold;"><?php echo htmlspecialchars($item['kuantiti_lulus']); ?></td>
    <td></td>
    <td class="center" style="color: red; font-weight: bold;"><?php echo htmlspecialchars($item['kuantiti_lulus']); ?></td>
    <td></td>
    </tr>
    <?php endwhile; ?>

    <?php 
    for ($i = $item_count; $i < 7; $i++): 
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
    </tr>
    <?php endfor; ?>
</tbody>
            
    <tfoot>
    <tr>
        <td colspan="3">
            <strong>Pemohon:</strong>
            <div class="signature-space"></div>
            (Tandatangan)<br>
            Nama&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php echo htmlspecialchars($header['nama_pemohon']); ?><br>
            Jawatan&nbsp;&nbsp;: <?php echo !empty($header['jawatan_pemohon']) ? htmlspecialchars($header['jawatan_pemohon']) : ''; ?><br>
            Tarikh&nbsp;&nbsp;&nbsp;: <?php echo formatMalayDate($header['tarikh_mohon']); ?>
        </td>
        <td colspan="3" style="border-left: 4px solid #000 !important;">
            <strong>Pegawai Pelulus:</strong>
            <div class="signature-space"></div>
            (Tandatangan)<br>
            Nama&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php echo htmlspecialchars($header['nama_pelulus'] ?? ''); ?><br>
            Jawatan&nbsp;&nbsp;:<br>
            Tarikh&nbsp;&nbsp;&nbsp;: <?php echo $header['tarikh_lulus'] ? formatMalayDate($header['tarikh_lulus']) : ''; ?>
        </td>
        <td colspan="2" style="border-left: 4px solid #000 !important;">
            <strong>Pemohon/ Wakil:</strong>
            <div class="signature-space"></div>
            (Tandatangan)<br>
            Nama&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:<br>
            Jawatan&nbsp;&nbsp;:<br>
            Tarikh&nbsp;&nbsp;&nbsp;:
        </td>
    </tr>
</tfoot>
        </table>

        <div class="footer-note">M.S. 12/13</div>

    </div> 
</body>
    
    <div class="no-print text-center mb-3" style="padding-top: 20px;">
        <button onclick="window.print()" class="btn btn-primary">Cetak Dokumen</button>
        <a href="manage_requests.php" class="btn btn-secondary">Kembali</a>
    </div>
</html>