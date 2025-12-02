<?php
// add_suppliers_to_products.php - Add random supplier names to products

require_once 'db.php';
require_once 'admin_auth_check.php';

echo "<h2>Adding Suppliers to Products</h2>";

// List of common office supply companies in Malaysia
$suppliers = [
    'Office Warehouse Sdn Bhd',
    'Puncak Niaga (M) Sdn Bhd',
    'Syarikat Meng Huat',
    'Econ Stationary Supplies',
    'SME Office Equipment'
];

echo "<h3>Available Suppliers:</h3>";
echo "<ul>";
foreach ($suppliers as $supplier) {
    echo "<li>" . htmlspecialchars($supplier) . "</li>";
}
echo "</ul>";

// Get all products
$products_query = "SELECT ID_produk, nama_produk, nama_pembekal FROM PRODUK ORDER BY ID_produk";
$products_result = $conn->query($products_query);

if (!$products_result) {
    die("<p style='color: red;'>Error: " . $conn->error . "</p>");
}

echo "<hr><h3>Assigning Suppliers to Products:</h3>";
echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'><th>Kod Item</th><th>Nama Produk</th><th>Pembekal Lama</th><th>Pembekal Baru</th><th>Status</th></tr>";

$updated_count = 0;
$skipped_count = 0;

while ($product = $products_result->fetch_assoc()) {
    $id_produk = $product['ID_produk'];
    $nama_produk = $product['nama_produk'];
    $old_pembekal = $product['nama_pembekal'];

    // If already has a supplier, skip it
    if (!empty($old_pembekal) && $old_pembekal !== '-') {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($id_produk) . "</td>";
        echo "<td>" . htmlspecialchars($nama_produk) . "</td>";
        echo "<td>" . htmlspecialchars($old_pembekal) . "</td>";
        echo "<td>-</td>";
        echo "<td style='color: orange;'>Skipped (already has supplier)</td>";
        echo "</tr>";
        $skipped_count++;
        continue;
    }

    // Randomly select a supplier
    $random_supplier = $suppliers[array_rand($suppliers)];

    // Update the product
    $update_stmt = $conn->prepare("UPDATE PRODUK SET nama_pembekal = ? WHERE ID_produk = ?");
    $update_stmt->bind_param("ss", $random_supplier, $id_produk);

    if ($update_stmt->execute()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($id_produk) . "</td>";
        echo "<td>" . htmlspecialchars($nama_produk) . "</td>";
        echo "<td style='color: gray;'>" . ($old_pembekal ?: 'NULL') . "</td>";
        echo "<td style='color: green; font-weight: bold;'>" . htmlspecialchars($random_supplier) . "</td>";
        echo "<td style='color: green;'>✓ Updated</td>";
        echo "</tr>";
        $updated_count++;
    } else {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($id_produk) . "</td>";
        echo "<td>" . htmlspecialchars($nama_produk) . "</td>";
        echo "<td>" . ($old_pembekal ?: 'NULL') . "</td>";
        echo "<td>-</td>";
        echo "<td style='color: red;'>Error: " . $conn->error . "</td>";
        echo "</tr>";
    }

    $update_stmt->close();
}

echo "</table>";

echo "<hr>";
echo "<h3>Summary:</h3>";
echo "<ul>";
echo "<li><strong>Total Products Processed:</strong> " . ($updated_count + $skipped_count) . "</li>";
echo "<li style='color: green;'><strong>Products Updated:</strong> " . $updated_count . "</li>";
echo "<li style='color: orange;'><strong>Products Skipped:</strong> " . $skipped_count . "</li>";
echo "</ul>";

$conn->close();

echo "<hr>";
echo "<p><a href='admin_products.php' class='btn btn-primary' style='display: inline-block; padding: 10px 20px; background: #4f46e5; color: white; text-decoration: none; border-radius: 5px;'>← Back to Products Page</a></p>";
echo "<p><small><em>Tip: Refresh this page to assign different random suppliers to the products that were updated.</em></small></p>";
?>

<style>
    body { font-family: Arial, sans-serif; padding: 20px; max-width: 1400px; margin: 0 auto; }
    h2 { color: #1f2937; border-bottom: 3px solid #4f46e5; padding-bottom: 10px; }
    h3 { color: #4f46e5; margin-top: 20px; }
    table { margin: 20px 0; }
    th { background: #4f46e5 !important; color: white !important; }
    tr:nth-child(even) { background: #f9fafb; }
    tr:hover { background: #e5e7eb; }
</style>
