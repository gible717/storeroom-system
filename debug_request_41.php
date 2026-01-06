<?php
// Debug script to check request #41 data
require 'db_connect.php';

$id_permohonan = 41;

echo "<h3>Debug Request #41</h3>";

// Check permohonan table
$stmt = $conn->prepare("SELECT ID_permohonan, ID_pemohon, nama_pemohon, jawatan_pemohon, tarikh_mohon, status FROM permohonan WHERE ID_permohonan = ?");
$stmt->bind_param("i", $id_permohonan);
$stmt->execute();
$result = $stmt->get_result();
$permohonan = $result->fetch_assoc();
$stmt->close();

echo "<h4>Permohonan Table Data:</h4>";
echo "<pre>";
print_r($permohonan);
echo "</pre>";

// Check staf table for this user
if ($permohonan) {
    $stmt2 = $conn->prepare("SELECT ID_staf, nama, jawatan FROM staf WHERE ID_staf = ?");
    $stmt2->bind_param("s", $permohonan['ID_pemohon']);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $staf = $result2->fetch_assoc();
    $stmt2->close();

    echo "<h4>Staf Table Data (Profile):</h4>";
    echo "<pre>";
    print_r($staf);
    echo "</pre>";
}

// Test the COALESCE query from kewps8_print.php
$stmt3 = $conn->prepare("SELECT
                            p.ID_permohonan,
                            p.nama_pemohon,
                            p.jawatan_pemohon as jawatan_from_request,
                            pemohon.jawatan as jawatan_from_profile,
                            COALESCE(NULLIF(p.jawatan_pemohon, ''), pemohon.jawatan) AS final_jawatan
                        FROM permohonan p
                        JOIN staf pemohon ON p.ID_pemohon = pemohon.ID_staf
                        WHERE p.ID_permohonan = ?");
$stmt3->bind_param("i", $id_permohonan);
$stmt3->execute();
$result3 = $stmt3->get_result();
$coalesce_test = $result3->fetch_assoc();
$stmt3->close();

echo "<h4>COALESCE Test (What kewps8_print.php will show):</h4>";
echo "<pre>";
print_r($coalesce_test);
echo "</pre>";

$conn->close();
?>
