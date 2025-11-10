<?php
// FILE: kewps8_confirm.php (The "Mini Receipt" / Cart)
$pageTitle = "Semak Permohonan";
require 'staff_header.php'; // This file MUST have session_start()

// --- 1. HANDLE ACTIONS (DELETE/EDIT) FIRST ---

// Check if an action was sent
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Handle Quantity Update ---
    if (isset($_POST['action']) && $_POST['action'] == 'update_quantity') {
        $no_kod_to_update = $_POST['no_kod'];
        $new_quantity = (int)$_POST['kuantiti'];

        if ($new_quantity > 0 && isset($_SESSION['cart'][$no_kod_to_update])) {
            // Update the quantity in the session
            $_SESSION['cart'][$no_kod_to_update]['kuantiti'] = $new_quantity;
        }
    }
    
    // Redirect back to this same page with a GET request to prevent form resubmission
    header('Location: kewps8_confirm.php');
    exit;
}

// --- Handle Delete (using a GET request for simplicity) ---
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $no_kod_to_delete = $_GET['no_kod'];
    
    if (isset($_SESSION['cart'][$no_kod_to_delete])) {
        // Remove the item from the cart
        unset($_SESSION['cart'][$no_kod_to_delete]);
    }
    
    // Redirect back to this same page
    header('Location: kewps8_confirm.php');
    exit;
}

// --- 2. GET CART ITEMS (to display on the page) ---
$cart_items = $_SESSION['cart'] ?? [];
$catatan = $_SESSION['request_catatan'] ?? '';

// If cart is empty, redirect back to the form
if (empty($cart_items)) {
    $_SESSION['error_msg'] = "Senarai permohonan anda kosong.";
    header('Location: kewps8_form.php');
    exit;
}
?>

<div class="position-relative text-center mb-4">
    <a href="kewps8_form.php" class="position-absolute top-50 start-0 translate-middle-y text-dark" title="Kembali & Tambah Item">
        <i class="bi bi-arrow-left fs-4"></i>
    </a>
    <h3 class="mb-0 fw-bold"><?php echo $pageTitle; ?></h3>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4 p-md-5">
                
                <h5 class="fw-bold mb-3">Sila Sahkan Senarai Permohonan Anda</h5>
                <p>Ini adalah "mini resit" untuk anda semak. Anda boleh kemaskini kuantiti atau padam item sebelum menghantar.</p>

                <form action="kewps8_form_process.php" method="POST">
                
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width: 5%;">Bil.</th>
                                    <th scope="col" style="width: 50%;">Perihal Stok</th>
                                    <th scope="col" class="text-center" style="width: 20%;">Kuantiti Dimohon</th>
                                    <th scope="col" class="text-center" style="width: 25%;">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $bil = 1;
                                foreach ($cart_items as $item): 
                                ?>
                                    <tr>
                                        <td><?php echo $bil++; ?>.</td>
                                        <td><?php echo htmlspecialchars($item['perihal_stok']); ?></td>
                                        
                                        <td classD="text-center">
                                            <form action="kewps8_confirm.php" method="POST" class="d-flex justify-content-center">
                                                <input type="hidden" name="action" value="update_quantity">
                                                <input type="hidden" name="no_kod" value="<?php echo $item['no_kod']; ?>">
                                                
                                                <input type="number" name="kuantiti" class="form-control" 
                                                       value="<?php echo $item['kuantiti']; ?>" 
                                                       min="1" style="width: 80px; text-align: center;">
                                                
                                                <button type="submit" class="btn btn-sm btn-outline-secondary ms-2" title="Kemaskini Kuantiti">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                        </td>
                                        
                                        <td class="text-center">
                                            <a href="kewps8_confirm.php?action=delete&no_kod=<?php echo $item['no_kod']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               title="Padam Item"
                                               onclick="return confirm('Adakah anda pasti mahu memadam item ini?');">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (!empty($catatan)): ?>
                    <hr class="my-4">
                    <h5 class="fw-bold mb-3">Catatan Permohonan</h5>
                    <div class="form-control" style="min-height: 100px;" readonly>
                        <?php echo nl2br(htmlspecialchars($catatan)); ?>
                    </div>
                    <?php endif; ?>

                    <hr class="my-4">
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle-fill me-2"></i>Sahkan & Hantar Permohonan
                        </button>
                    </div>

                </form> </div>
        </div>
    </div>
</div>

<?php 
require 'staff_footer.php'; 
?>