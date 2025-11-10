<?php
// FILE: kewps8_form.php
$pageTitle = "Borang Permohonan Stok";
require 'staff_header.php'; // Use staff header

// --- 1. Get Logged-in Staff Details ---
// We get all the data we need from the session and database
$staff_id = $_SESSION['ID_staf'];
$stmt = $conn->prepare("SELECT staf.nama, staf.jawatan, staf.ID_jabatan, jabatan.nama_jabatan 
                        FROM staf 
                        LEFT JOIN jabatan ON staf.ID_jabatan = jabatan.ID_jabatan 
                        WHERE staf.ID_staf = ?");
$stmt->bind_param("s", $staff_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Store details in variables for easy use in the form
$nama_pemohon = $user['nama'];
$jawatan_pemohon = $user['jawatan'];
$nama_jabatan = $user['nama_jabatan'];
$id_jabatan = $user['ID_jabatan'];

// --- 2. Get All 'Barang' (Items) from Database ---
// We need this to populate the "Add Item" dropdown
$barang_list = [];
$result = $conn->query("SELECT no_kod, perihal_stok, unit_pengukuran FROM barang ORDER BY perihal_stok ASC");
while ($row = $result->fetch_assoc()) {
    $barang_list[] = $row;
}
$conn->close();
?>

<style>
    #item_table th, #item_table td {
        vertical-align: middle;
    }
    .item-row {
        border-bottom: 1px solid #eee;
    }
    .item-row:last-child {
        border-bottom: none;
    }
    /* The CSS that hid the number arrows has been deleted. */
</style>

<div class="position-relative text-center mb-4">
    <a href="staff_dashboard.php" class="position-absolute top-50 start-0 translate-middle-y text-dark" title="Kembali">
        <i class="bi bi-arrow-left fs-4"></i>
    </a>
    <h3 class="mb-0 fw-bold"><?php echo $pageTitle; ?></h3>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4 p-md-5">

        <form action="kewps8_form_process.php" method="POST" id="kewps8_form">

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
                <div class="col-md-6">
                    <label class="form-label">Jawatan</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($jawatan_pemohon); ?>" disabled readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tarikh Mohon</label>
                    <input type="text" class="form-control" value="<?php echo date('d/m/Y'); ?>" disabled readonly>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Senarai Permohonan Barang</h5>
                <button type="button" class="btn btn-outline-primary" id="add_item_btn">
                    <i class="bi bi-plus-lg me-2"></i>Tambah Item
                </button>
            </div>

            <div class="table-responsive">
                <table class="table" id="item_table">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width: 5%;">Bil.</th>
                            <th scope="col" style="width: 60%;">Perihal Stok</th>
                            <th scope="col" style="width: 20%;">Kuantiti Dimohon</th>
                            <th scope="col" class="text-center" style="width: 15%;">Padam</th>
                        </tr>
                    </thead>
                    <tbody id="item_list_body">
                    </tbody>
                </table>
            </div>

            <hr class="my-4">
            <div class="mb-3">
                <label for="catatan" class="form-label">Catatan (Optional)</label>
                <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Tambah catatan atau maklumat tambahan..."></textarea>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary btn-lg">
                    Hantar Permohonan
                </button>
            </div>

        </form>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {

    const addItemBtn = document.getElementById('add_item_btn');
    const itemListBody = document.getElementById('item_list_body');
    const kewps8Form = document.getElementById('kewps8_form');

    // 1. We store the PHP barang_list in a JavaScript variable
    // We use json_encode to safely convert PHP array to JavaScript array
    const allItems = <?php echo json_encode($barang_list); ?>;

    // This is our item counter
    let itemCounter = 0;

    // 2. Function to add a new row
    function addNewRow() {
        itemCounter++;

        const row = document.createElement('tr');
        row.className = 'item-row';
        row.id = 'row_' + itemCounter;

        // --- Cell 1: Bilangan (Number) ---
        row.innerHTML = `
            <td class="pt-3">
                <strong>${itemCounter}.</strong>
            </td>
        `;

        // --- Cell 2: Item Select Dropdown ---
        // Create a <select> element
        const select = document.createElement('select');
        select.name = 'items[${itemCounter}][no_kod]'; // e.g., items[1][no_kod]
        select.className = 'form-select';
        select.required = true;

        // Add a "placeholder" option
        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = '--- Pilih Barang ---';
        placeholderOption.disabled = true;
        placeholderOption.selected = true;
        select.appendChild(placeholderOption);

        // Add all items from our database list
        allItems.forEach(item => {
            const option = document.createElement('option');
            option.value = item.no_kod;
            // Display "No. Kod - Perihal Stok (Unit)"
            option.textContent = item.perihal_stok;
            select.appendChild(option);
        });

        const cell2 = document.createElement('td');
        cell2.appendChild(select);
        row.appendChild(cell2);

        // --- Cell 3: Kuantiti (Quantity) ---
        row.innerHTML += `
            <td>
                <input type="number" name="items[${itemCounter}][kuantiti_mohon]" class="form-control" value="1" min="1" required>
            </td>
        `;

        // --- Cell 4: Delete Button ---
        const cell4 = document.createElement('td');
        const deleteBtn = document.createElement('button');
        deleteBtn.type = 'button';
        deleteBtn.className = 'btn btn-outline-danger';
        deleteBtn.innerHTML = '<i class="bi bi-trash-fill"></i>';
        
        // Add an event listener to the delete button
        deleteBtn.onclick = function() {
            row.remove();
            updateRowNumbers();
        };
        cell4.appendChild(deleteBtn);
        row.appendChild(cell4);

        // Add the new row to the table body
        itemListBody.appendChild(row);
    }

    // 3. Function to update row numbers after one is deleted
    function updateRowNumbers() {
        const rows = itemListBody.getElementsByTagName('tr');
        for (let i = 0; i < rows.length; i++) {
            rows[i].getElementsByTagName('td')[0].innerHTML = `
                <strong class="pt-3">${i + 1}.</strong>
            `;
        }
    }

    // 4. Listen for the "Add Item" button click
    addItemBtn.addEventListener('click', function() {
        addNewRow();
    });

    // 5. Add one row by default when the page loads
    addNewRow();

    // 6. Form validation on submit
    kewps8Form.addEventListener('submit', function(e) {
        if (itemListBody.getElementsByTagName('tr').length === 0) {
            e.preventDefault(); // Stop the form
            Swal.fire({
                title: 'Ralat',
                text: 'Sila tambah sekurang-kurangnya satu barang untuk permohonan.',
                icon: 'error'
            });
        }
    });

});
</script>

<?php 
require 'staff_footer.php'; 
?>