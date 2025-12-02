<?php
// bulk_update_toner.php - Bulk update all uncategorized products to Toner category

require_once 'db.php';

echo "<h2>Bulk Update Products to Toner Category</h2>";

// Step 1: Check if Toner category exists
$toner_check = $conn->query("SELECT ID_kategori, nama_kategori FROM kategori WHERE nama_kategori = 'Toner'");

if ($toner_check->num_rows == 0) {
    echo "<p style='color: red;'>ERROR: 'Toner' category not found! Please add it first.</p>";
    exit;
}

$toner = $toner_check->fetch_assoc();
$toner_id = $toner['ID_kategori'];
echo "<p><strong>Toner Category Found:</strong> ID = {$toner_id}, Name = {$toner['nama_kategori']}</p>";

// Step 2: Find products without category
$uncategorized = $conn->query("SELECT ID_produk, nama_produk, ID_kategori FROM PRODUK WHERE ID_kategori IS NULL OR ID_kategori = '' OR ID_kategori = 0");
$count = $uncategorized->num_rows;

echo "<p><strong>Found {$count} uncategorized products:</strong></p>";
echo "<ul>";
while ($product = $uncategorized->fetch_assoc()) {
    echo "<li>{$product['ID_produk']} - {$product['nama_produk']}</li>";
}
echo "</ul>";

// Step 3: Update all uncategorized products to Toner
if ($count > 0) {
    $update_sql = "UPDATE PRODUK SET ID_kategori = ? WHERE ID_kategori IS NULL OR ID_kategori = '' OR ID_kategori = 0";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $toner_id);

    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        echo "<p style='color: green; font-weight: bold;'>✓ SUCCESS: Updated {$affected} products to 'Toner' category!</p>";
    } else {
        echo "<p style='color: red;'>ERROR: " . $conn->error . "</p>";
    }
    $stmt->close();
} else {
    echo "<p>No products to update.</p>";
}

// Step 4: Verify the update
echo "<hr><h3>Verification - Products with Toner category:</h3>";
$verify = $conn->query("SELECT p.ID_produk, p.nama_produk, k.nama_kategori
                        FROM PRODUK p
                        LEFT JOIN kategori k ON p.ID_kategori = k.ID_kategori
                        WHERE k.nama_kategori = 'Toner'");

echo "<p>Total products with Toner category: <strong>{$verify->num_rows}</strong></p>";
echo "<ul>";
while ($product = $verify->fetch_assoc()) {
    echo "<li>{$product['ID_produk']} - {$product['nama_produk']} ({$product['nama_kategori']})</li>";
}
echo "</ul>";

$conn->close();

echo "<hr><p><a href='admin_products.php'>← Back to Products Page</a></p>";
?>
