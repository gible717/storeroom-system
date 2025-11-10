<?php
// FILE: kewps8_print.php
// This file is for PRINTING the KEW.PS-8 form.
session_start();
require_once __DIR__ . '/db.php';

// Check if user is logged in (staff or admin can print)
if (!isset($_SESSION['ID_staf'])) {
    die("Sila log masuk.");
}

// 1. Get Request ID & Validate
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID Permohonan tidak sah.");
}
$id_permohonan = (int)$_GET['id'];

// 2. Fetch 'permohonan' (Header) Data
$stmt_header = $conn->prepare("SELECT p.*, j.nama_jabatan 
                               FROM permohonan p
                               LEFT JOIN jabatan j ON p.ID_jabatan = j.ID_jabatan
                               WHERE p.ID_permohonan = ?");
$stmt_header->bind_param("i", $id_permohonan);
$stmt_header->execute();
$permohonan = $stmt_header->get_result()->fetch_assoc();
$stmt_header->close();

if (!$permohonan) {
    die("Permohonan tidak dijumpai.");
}

// 3. Fetch 'permohonan_barang' (Items) Data
$stmt_items = $conn->prepare("SELECT pb.*, b.no_kod, b.perihal_stok, b.unit_pengukuran 
                             FROM permohonan_barang pb
                             LEFT JOIN barang b ON pb.no_kod = b.no_kod
                             WHERE pb.ID_permohonan = ?");
$stmt_items->bind_param("i", $id_permohonan);
$stmt_items->execute();
$items_result = $stmt_items->get_result();
$conn->close();

// Store items in an array for looping
$items = [];
while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-M-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak KEW.PS-8 (ID: <?php echo $id_permohonan; ?>)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* This is the core CSS for the "look and feel" */
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt; /* Standard font size for official docs */
            color: #000;
            background-color: #eee; /* Grey background for the screen */
        }
        .page-container {
            width: 29.7cm; /* A4 Landscape width */
            min-height: 21cm; /* A4 Landscape height */
            padding: 1.5cm;
            margin: 2rem auto;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        .form-header .doc-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: right;
        }
        .form-header .doc-code {
            font-size: 12pt;
            font-weight: bold;
            border: 1px solid #000;
            padding: 5px 10px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 1.5rem;
            border: 1px solid #000;
            border-collapse: collapse;
        }
        .info-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }
        .info-table .label {
            width: 25%;
            font-weight: bold;
            background-color: #f3f3f3;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }
        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
        }
        .items-table th {
            font-weight: bold;
            background-color: #f3f3f3;
        }
        .items-table .text-left {
            text-align: left;
        }
        /* Make sure the item rows are tall enough */
        .items-table .item-row td {
            height: 40px; 
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 2rem;
        }
        .signature-box {
            width: 32%;
            border: 1px solid #000;
            padding: 10px;
        }
        .signature-box .title {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 4rem; /* Space for the signature */
        }
        .signature-box .info {
            font-size: 10pt;
            border-top: 1px solid #000;
            padding-top: 8px;
        }
        .signature-box .info div {
            line-height: 1.5;
        }

        .print-button-container {
            text-align: center;
            padding: 1rem;
            background: #333;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 100;
        }

        /* --- PRINT STYLES --- */
        @media print {
            /* This is the magic for horizontal printing */
            @page {
                size: A4 landscape;
                margin: 0;
            }
            body {
                background-color: #fff;
            }
            .print-button-container {
                display: none; /* Hide the print button */
            }
            .page-container {
                width: 100%;
                min-height: 0;
                margin: 0;
                padding: 1.5cm; /* Apply padding to the printable area */
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>

    <div class="print-button-container">
        <button class="btn btn-primary btn-lg" onclick="window.print()">Cetak Dokumen</button>
        <button class="btn btn-secondary btn-lg" onclick="window.close()">Tutup</button>
    </div>

    <div class="page-container">
        <div class="form-header">
            <div>
                <img src="path/to/your/logo.png" alt="Logo" style="width: 150px;">