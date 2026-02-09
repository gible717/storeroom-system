<?php
// print_request.php - Print request document

require 'db.php';
session_start();

// Malay month formatting function
function format_malay_date($date_string) {
    $malay_months = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sep', 'Okt', 'Nov', 'Dis'];
    $date = strtotime($date_string);
    $day = date('d', $date);
    $month_index = (int)date('n', $date) - 1;
    $year = date('Y', $date);
    return $day . ' ' . $malay_months[$month_index] . ' ' . $year;
}

// Check login
if (!isset($_SESSION['ID_staf'])) {
    die("Akses tidak dibenarkan. Sila log masuk.");
}
$userID = $_SESSION['ID_staf'];
$isAdmin = $_SESSION['is_admin'] ?? 0;
$userName = $_SESSION['nama'];

// Get request ID
$request_id = $_GET['id'] ?? null;
if (!$request_id) {
    die("ID Permohonan tidak dijumpai.");
}

// Fetch request data
$sql = "SELECT
            pr.*,
            s_pemohon.nama AS nama_pemohon,
            pr.jabatan_unit,
            p.nama_produk,
            p.ID_produk
        FROM permohonan pr
        JOIN staf s_pemohon ON pr.ID_staf = s_pemohon.ID_staf
        JOIN produk p ON pr.ID_produk = p.ID_produk
        WHERE pr.ID_permohonan = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();

if (!$request) {
    die("Rekod permohonan tidak dijumpai.");
}

// 4. "STEAK" (FIX) 2: "Slay" (fix) the "4x4" (safe) security check
// An Admin ($isAdmin == 1) can "vibe" (see) everything.
// A "bland food" (Staff) ($isAdmin == 0) can only "vibe" (see) their *own* request.
if ($isAdmin != 1 && $request['ID_staf'] !== $userID) {
    die("Anda tidak mempunyai kebenaran untuk melihat rekod ini.");
}
// --- END OF "STEAK" (FIX) ---

// 5. GET APPROVER'S NAME
$approver_name = "-";
if ($request['status'] === 'Diluluskan' || $request['status'] === 'Selesai') {
    $approver_name = "Pejabat Pentadbiran";
}

?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Permohonan #<?php echo str_pad($request['ID_permohonan'], 4, '0', STR_PAD_LEFT); ?></title>
    <link rel="icon" type="image/png" href="assets/img/favicon-32.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .print-container { max-width: 800px; margin: 2rem auto; background: #ffffff; border: 1px solid #dee2e6; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .print-header { padding: 2rem; border-bottom: 2px solid #000; text-align: center; }
        .print-header h3 { margin: 0; font-weight: bold; }
        .print-body { padding: 2.5rem; }
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .details-box { background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 0.5rem; padding: 1.5rem; }
        .details-box h5 { font-weight: bold; border-bottom: 1px solid #dee2e6; padding-bottom: 0.5rem; margin-bottom: 1rem; }
        .details-box p { margin-bottom: 0.5rem; }
        .details-box strong { display: inline-block; width: 130px; }
        .item-table { margin-top: 2rem; }
        .signature-boxes { margin-top: 3rem; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; text-align: center; }
        .signature-box { border-top: 1px solid #000; padding-top: 1rem; font-weight: bold; }
        .print-actions { text-align: center; padding: 1rem 2rem 2rem; }
        @media print {
            body {
                background-color: #ffffff;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-container {
                margin: 0;
                border: none;
                box-shadow: none;
                width: 100% !important;
                max-width: none !important;
                transform: scale(1) !important;
                transform-origin: top left;
            }
            .print-actions { display: none; }
            table {
                width: 100% !important;
                table-layout: fixed;
            }
        }
    </style>
</head>
<body>

    <div class="print-container">
        <div class="print-header">
            <h3>BORANG PERMOHONAN STOK</h3>
            <p class="mb-0">Sistem Pengurusan Stor & Inventori</p>
        </div>

        <div class="print-body">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <strong>ID Permohonan:</strong> REQ<?php echo str_pad($request['ID_permohonan'], 4, '0', STR_PAD_LEFT); ?><br>
                    <strong>Tarikh Mohon:</strong> <?php echo format_malay_date($request['tarikh_mohon']); ?>
                </div>
                <div>
                    <strong>Status:</strong> 
                    <span class="badge 
                        <?php 
                            if ($request['status'] === 'Diluluskan') echo 'bg-success';
                            elseif ($request['status'] === 'Ditolak') echo 'bg-danger';
                            else echo 'bg-secondary';
                        ?>
                    "><?php echo htmlspecialchars($request['status']); ?></span>
                </div>
            </div>

            <div class="details-grid">
                <div class="details-box">
                    <h5>Butiran Pemohon</h5>
                    <p><strong>Nama:</strong> <?php echo htmlspecialchars($request['nama_pemohon']); ?></p>
                    <p><strong>Jabatan/Unit:</strong> <?php echo htmlspecialchars($request['jabatan_unit']); ?></p>
                </div>
                <div class="details-box">
                    <h5>Butiran Kelulusan</h5>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($request['status']); ?></p>
                    <p><strong>Diluluskan Oleh:</strong> <?php echo htmlspecialchars($approver_name); ?></p>
                    <p><strong>Tarikh Selesai:</strong> <?php echo $request['tarikh_selesai'] ? format_malay_date($request['tarikh_selesai']) . ', ' . date('g:ia', strtotime($request['tarikh_selesai'])) : '-'; ?></p>
                </div>
            </div>

            <div class="item-table">
                <h5>Butiran Item Dimohon</h5>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">ID Produk</th>
                            <th scope="col">Nama Produk</th>
                            <th scope="col">Kuantiti Diminta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td><?php echo htmlspecialchars($request['ID_produk']); ?></td>
                            <td><?php echo htmlspecialchars($request['nama_produk']); ?></td>
                            <td><?php echo htmlspecialchars($request['jumlah_diminta']); ?> unit</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div classs="signature-boxes">
                <div class="signature-box">
                    (Tandatangan Pemohon)
                </div>
                <div class="signature-box">
                    (Tandatangan Pegawai Stor)
                </div>
            </div>

        </div>
        
        <div class="print-actions">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer-fill me-2"></i>Cetak Dokumen
            </button>
            <a href="javascript:window.close()" class="btn btn-light">Tutup</a>
        </div>
    </div>

    <script>
        // Automatically open the print dialog once the page is loaded
        window.onload = function() {
            window.print();
        };
    </script>

</body>
</html>