<?php
// FILE: admin_category.php (NOW "SLAYED" WITH NEW LAYOUT)
$pageTitle = "Pengurusan Kategori";
require 'admin_header.php'; // This "slays" (includes) db.php

// "Boring" (Logic): Get all categories to display in the list
$kategori_list_sql = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
$kategori_list_result = $conn->query($kategori_list_sql);
?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center mb-4">
        <a href="admin_products.php" class="btn btn-link nav-link p-0 me-3" title="Kembali">
            <i class="bi bi-arrow-left" style="font-size: 1.5rem; color: #858796;"></i>
        </a>
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Pengurusan Kategori</h1>
    </div>

    <div class="row">
    
        <div class="col-lg-8 col-md-7 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-header bg-white py-3" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                    <h6 class="m-0 fw-bold text-primary">Senarai Kategori Sedia Ada</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4">Nama Kategori</th>
                                    <th scope="col" class="text-end pe-4">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($kategori_list_result && $kategori_list_result->num_rows > 0) {
                                    while ($row = $kategori_list_result->fetch_assoc()) {
                                ?>
                                    <tr>
                                        <td class="align-middle ps-4"><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                                        <td class="text-end pe-4">
                                            <form action="admin_category_process.php" method="POST" class="d-inline" onsubmit="return confirm('Anda pasti mahu padam kategori ini?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="ID_kategori" value="<?php echo $row['ID_kategori']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='2' class='text-center p-4'>Tiada kategori ditemui. Sila tambah kategori baru.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-5 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-header bg-primary text-white" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                    Tambah Kategori Baru
                </div>
                <div class="card-body p-4">
                    <form action="admin_category_process.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="nama_kategori" class="form-label fw-bold">Nama Kategori</label>
                            <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" placeholder="Cth: Toner" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle-fill"></i> Tambah
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
require 'admin_footer.php';
?>