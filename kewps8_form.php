<?php
// FILE: kewps8_form.php (VERSI 4.0 - "Shopping Cart")
$pageTitle = "Borang Permohonan Stok";
require 'staff_header.php'; // This file MUST have session_start() at the top

// --- 1. Get Logged-in Staff Details ---
$staff_id = $_SESSION['ID_staf'];
$stmt = $conn->prepare("SELECT staf.nama, staf.jawatan, staf.ID_jabatan, jabatan.nama_jabatan 
                        FROM staf 
                        LEFT JOIN jabatan ON staf.ID_jabatan = jabatan.ID_jabatan 
                        WHERE staf.ID_staf = ?");
$stmt->bind_param("s", $staff_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$nama_pemohon = $user['nama'];
$jawatan_pemohon = $user['jawatan'];
$nama_jabatan = $user['nama_jabatan'];

// --- 2. Get All 'Barang' (Items) from Database ---
$barang_list = [];
$result = $conn->query("SELECT no_kod, perihal_stok, unit_pengukuran FROM barang WHERE baki_semasa > 0 ORDER BY perihal_stok ASC");
while ($row = $result->fetch_assoc()) {
    $barang_list[] = $row;
}
$conn->close();

// --- 3. NEW CART LOGIC ---
// Clear any old cart items when we first load the form
unset($_SESSION['cart']);
unset($_SESSION['request_catatan']);
$_SESSION['cart'] = [];
?>

<div class="position-relative text-center mb-4">
    <a href="staff_dashboard.php" class="position-absolute top-50 start-0 translate-middle-y text-dark" title="Kembali">
        <i class="bi bi-arrow-left fs-4"></i>
    </a>
    <h3 class="mb-0 fw-bold"><?php echo $pageTitle; ?></h3>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4 p-md-5">

                <div id="add_item_form">
                    
                    <h5 class="fw-bold mb-3">Maklumat Pemohon</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Nama Pemohon</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($nama_pemohon); ?>" disabled readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jabatan / Unit</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($nama_jabatan); ?>" disabled readonly>
                        </div>
                    </div>

                    <hr>

                    <h5 class="fw-bold mb-3">Tambah Item</h5>
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="item_select" class="form-label">*Perihal Stok</label>
                            <select class="form-select" id="item_select">
                                <option value="" selected disabled>--- Pilih Barang ---</option>
                                <?php foreach ($barang_list as $item): ?>
                                    <option value="<?php echo $item['no_kod']; ?>" data-unit="<?php echo htmlspecialchars($item['unit_pengukuran']); ?>">
                                        <?php echo htmlspecialchars($item['perihal_stok']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="item_quantity" class="form-label">*Kuantiti Dimohon</label>
                            <input type="number" class="form-control" id="item_quantity" value="1" min="1">
                        </div>

                        <div class="col-12">
                            <label for="item_catatan" class="form-label">Catatan (Optional)</label>
                            <textarea class="form-control" id="item_catatan" rows="3" placeholder="Tambah catatan atau maklumat tambahan untuk permohonan ini..."></textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-primary" id="add_item_ajax_btn">
                            <i class="bi bi-plus-lg me-2"></i>Tambah Item
                        </button>
                        
                        <a href="kewps8_confirm.php" class="btn btn-success btn-lg" id="review_cart_btn" style="display: none;">
                            Selesai & Semak
                        </a>
                    </div>

                </div> </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const addItemBtn = document.getElementById('add_item_ajax_btn');
    const reviewCartBtn = document.getElementById('review_cart_btn');
    const itemSelect = document.getElementById('item_select');
    const itemQuantity = document.getElementById('item_quantity');
    const itemCatatan = document.getElementById('item_catatan');

    let itemsInCart = 0;

    addItemBtn.addEventListener('click', function() {
        
        const no_kod = itemSelect.value;
        const kuantiti = itemQuantity.value;
        const catatan = itemCatatan.value;
        const perihal_stok = itemSelect.options[itemSelect.selectedIndex].text;

        // --- Validation ---
        if (no_kod === "") {
            Swal.fire('Ralat', 'Sila pilih barang.', 'error');
            return;
        }
        if (kuantiti <= 0) {
            Swal.fire('Ralat', 'Kuantiti mestilah 1 atau lebih.', 'error');
            return;
        }

        // Disable button to prevent double-click
        addItemBtn.disabled = true;
        addItemBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menambah...';

        // --- AJAX Fetch (sends data to our new processor) ---
        fetch('kewps8_cart_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                no_kod: no_kod,
                kuantiti: kuantiti,
                perihal_stok: perihal_stok, // We send text for the session
                catatan: catatan
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                itemsInCart = data.cart_count;
                
                // Show success popup
                Swal.fire({
                    title: 'Berjaya!',
                    text: `"${perihal_stok}" telah ditambah ke senarai anda.`,
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });

                // Clear fields for next item
                itemSelect.value = '';
                itemQuantity.value = '1';
                // We keep the Catatan field, as it's for the whole request

                // Show and update the "Review" button
                reviewCartBtn.style.display = 'block';
                reviewCartBtn.innerHTML = `Selesai & Semak (${itemsInCart})`;

            } else {
                Swal.fire('Ralat', data.message || 'Gagal menambah item.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Ralat', 'Gagal menghubungi server.', 'error');
        })
        .finally(() => {
            // Re-enable button
            addItemBtn.disabled = false;
            addItemBtn.innerHTML = '<i class="bi bi-plus-lg me-2"></i>Tambah Item';
        });
    });

});
</script>

<?php 
require 'staff_footer.php'; 
?>