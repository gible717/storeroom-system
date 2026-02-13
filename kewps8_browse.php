<?php
session_start();
require_once 'db.php';

// Check login
if (!isset($_SESSION['ID_staf'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = "Pilih Item";

// Load header based on role
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    require 'admin_header.php';
} else {
    require 'staff_header.php';
}

// Get categories for filter (main categories)
$kategori_sql = "SELECT DISTINCT kategori FROM barang WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC";
$kategori_result = $conn->query($kategori_sql);
$categories = [];
if ($kategori_result) {
    while ($row = $kategori_result->fetch_assoc()) {
        $categories[] = $row['kategori'];
    }
}

// Get all unique subcategory names (for independent brand/sub filter row)
$all_subcategories = [];
$allsub_sql = "SELECT DISTINCT k.nama_kategori AS sub_name
    FROM KATEGORI k
    WHERE k.parent_id IS NOT NULL
    ORDER BY k.nama_kategori ASC";
$allsub_result = $conn->query($allsub_sql);
if ($allsub_result) {
    while ($row = $allsub_result->fetch_assoc()) {
        $all_subcategories[] = $row['sub_name'];
    }
}

// Get all products with subcategory info
$products = [];
$result = $conn->query("
    SELECT b.no_kod, b.perihal_stok, b.kategori, b.baki_semasa AS stok_semasa, b.gambar_produk,
           k.nama_kategori AS sub_nama, k.parent_id
    FROM barang b
    LEFT JOIN KATEGORI k ON b.ID_kategori = k.ID_kategori
    ORDER BY b.perihal_stok ASC
");
while ($row = $result->fetch_assoc()) {
    // If parent_id is set, product is in a subcategory
    $row['subkategori'] = (!empty($row['parent_id'])) ? $row['sub_nama'] : '';
    $products[] = $row;
}

// Initialize cart session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart_count = count($_SESSION['cart']);

// Back link based on role
$back_link = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) ? 'manage_requests.php' : 'staff_dashboard.php';
?>

<!-- ========================= PAGE-SPECIFIC STYLES ========================= -->
<?php
// Colour palette based on role (admin = indigo, staff = blue)
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
$accent       = $is_admin ? '#4f46e5' : '#0d6efd';
$accent_light = $is_admin ? '#eef2ff' : '#e7f1ff';
$accent_rgb   = $is_admin ? '79, 70, 229' : '13, 110, 253';
?>
<style>
:root {
    --browse-accent: <?php echo $accent; ?>;
    --browse-accent-light: <?php echo $accent_light; ?>;
    --browse-accent-rgb: <?php echo $accent_rgb; ?>;
}

/* Override Bootstrap primary to match system accent */
.browse-content .btn-primary,
.cart-bar .btn-primary,
.modal .btn-primary {
    background-color: var(--browse-accent);
    border-color: var(--browse-accent);
}
.browse-content .btn-primary:hover,
.cart-bar .btn-primary:hover,
.modal .btn-primary:hover {
    background-color: var(--browse-accent);
    border-color: var(--browse-accent);
    filter: brightness(1.1);
}
.browse-content .btn-outline-primary {
    color: var(--browse-accent);
    border-color: var(--browse-accent);
}
.browse-content .btn-outline-primary:hover {
    background-color: var(--browse-accent);
    border-color: var(--browse-accent);
    color: #fff;
}
.modal .badge.bg-primary {
    background-color: var(--browse-accent) !important;
}
.cart-bar .btn-outline-secondary:hover {
    background-color: #dc3545;
    border-color: #dc3545;
    color: #fff;
}

/* --- Product Card Grid --- */
.product-card {
    border: none;
    border-radius: 1rem;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
}

/* Product image area */
.product-card .card-img-top {
    height: 180px;
    object-fit: cover;
    background-color: #f1f3f5;
}

/* Placeholder when no image */
.product-img-placeholder {
    height: 180px;
    background: linear-gradient(135deg, #f1f3f5 0%, #e9ecef 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
    font-size: 3rem;
}

/* Product name - clamp to 2 lines */
.product-name {
    font-size: 0.95rem;
    font-weight: 600;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 2.66em; /* 2 lines worth */
}


/* Quantity stepper */
.qty-stepper {
    max-width: 130px;
    margin: 0 auto;
}
.qty-stepper .form-control {
    text-align: center;
    font-weight: 600;
    font-size: 0.9rem;
    border-color: #adb5bd;
    -moz-appearance: textfield;
}
.qty-stepper .form-control::-webkit-outer-spin-button,
.qty-stepper .form-control::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
.qty-stepper .btn {
    z-index: 0;
    border-color: #adb5bd;
    color: #6c757d;
}
.qty-stepper .btn:hover {
    background: #6c757d;
    border-color: #6c757d;
    color: #fff;
}
.qty-stepper .btn:active,
.qty-stepper .btn:focus,
.qty-stepper .btn.active {
    background: #6c757d !important;
    border-color: #6c757d !important;
    color: #fff !important;
}

/* Add to Cart button - white default, accent on hover */
.btn-add-cart {
    background: #fff;
    color: var(--browse-accent);
    border: 1.5px solid var(--browse-accent);
    transition: all 0.2s ease;
}
.btn-add-cart:hover {
    background: var(--browse-accent);
    color: #fff;
    border-color: var(--browse-accent);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(var(--browse-accent-rgb), 0.35);
}

/* Category filter pills */
.filter-pill {
    border: 1.5px solid #dee2e6;
    border-radius: 50px;
    padding: 0.4rem 1rem;
    font-size: 0.8rem;
    font-weight: 500;
    color: #495057;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}
.filter-pill:hover {
    border-color: var(--browse-accent);
    color: var(--browse-accent);
    background: var(--browse-accent-light);
}
.filter-pill.active {
    border-color: var(--browse-accent);
    background: var(--browse-accent);
    color: #fff;
}

/* Subcategory filter pills - different color (teal/warm) + slightly smaller */
.sub-pill {
    font-size: 0.75rem !important;
    padding: 0.3rem 0.85rem !important;
}
.sub-pill:hover {
    border-color: #0d9488 !important;
    color: #0d9488 !important;
    background: #f0fdfa !important;
}
.sub-pill.active {
    border-color: #0d9488 !important;
    background: #0d9488 !important;
    color: #fff !important;
}

/* Search bar */
.search-browse {
    max-width: 350px;
}

/* Sticky bottom cart bar */
.cart-bar {
    position: fixed;
    bottom: 0;
    right: 0;
    background: #fff;
    border-top: 2px solid #e5e7eb;
    padding: 0.75rem 2rem;
    z-index: 1030;
    box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease;
    /* Default: full width for staff (no sidebar) */
    left: 0;
}
.cart-bar.hidden {
    transform: translateY(100%);
}

/* Admin layout: offset by sidebar width on desktop */
.cart-bar.has-sidebar {
    left: 0; /* mobile: full width */
}
@media (min-width: 992px) {
    .cart-bar.has-sidebar {
        left: 280px; /* desktop: respect sidebar */
    }
}

/* Bottom padding to prevent cart bar from covering content */
.browse-content {
    padding-bottom: 80px;
}

/* Added-to-cart animation */
@keyframes addPulse {
    0% { transform: scale(1); }
    50% { transform: scale(0.95); }
    100% { transform: scale(1); }
}
.product-card.just-added {
    animation: addPulse 0.3s ease;
}
.product-card.in-cart {
    outline: 2px solid var(--browse-accent);
    outline-offset: -2px;
}

/* Cart count badge */
.cart-count-badge {
    background: var(--browse-accent);
    color: #fff;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6c757d;
}
.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.4;
}
</style>

<!-- ========================= PAGE CONTENT ========================= -->
<div class="browse-content">

    <!-- Header: Back + Title + Cart Icon -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="<?php echo $back_link; ?>" class="text-dark me-3" title="Kembali">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <div>
                <h3 class="mb-0 fw-bold">Pilih Item</h3>
                <small class="text-muted">Sila Pilih barang yang ingin dimohon</small>
            </div>
        </div>
        <button class="btn btn-outline-primary position-relative" id="cartPreviewBtn" title="Lihat Senarai">
            <i class="bi bi-cart3 fs-5"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="cartBadge">
                0
            </span>
        </button>
    </div>

    <!-- Filter Bar: Category Pills + Search -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 1rem;">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <!-- Category Filter Pills -->
                <div class="d-flex flex-wrap gap-2 align-items-center" id="categoryFilters">
                    <button class="filter-pill active" data-kategori="">
                        <i class="bi bi-grid-fill me-1"></i>Semua
                    </button>
                    <?php foreach ($categories as $kategori): ?>
                        <button class="filter-pill" data-kategori="<?php echo htmlspecialchars($kategori); ?>">
                            <?php echo htmlspecialchars($kategori); ?>
                        </button>
                    <?php endforeach; ?>
                    <small class="text-muted ms-1" style="font-size: 0.68rem; opacity: 0.7;"><i class="bi bi-info-circle me-1"></i>Boleh pilih lebih dari satu</small>
                </div>

                <!-- Search -->
                <div class="input-group search-browse">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="searchInput"
                        placeholder="Cari nama atau kod produk...">
                </div>
            </div>

            <?php if (!empty($all_subcategories)): ?>
            <!-- Subcategory/Brand Filter Pills (always visible, independent filter) -->
            <div class="mt-3 pt-3 border-top" id="subcategoryRow">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <small class="text-muted me-1"><i class="bi bi-tag me-1"></i>Subkategori:</small>
                    <button class="filter-pill sub-pill active" data-sub="">Semua</button>
                    <?php foreach ($all_subcategories as $sub): ?>
                        <button class="filter-pill sub-pill" data-sub="<?php echo htmlspecialchars($sub); ?>">
                            <?php echo htmlspecialchars($sub); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Product Count Info -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <small class="text-muted" id="productCount">
            Menunjukkan <?php echo count($products); ?> produk
        </small>
        <!-- Optional: Sort dropdown -->
        <select class="form-select form-select-sm" style="width: auto;" id="sortSelect">
            <option value="name-asc">Nama (A-Z)</option>
            <option value="name-desc">Nama (Z-A)</option>
        </select>
    </div>

    <!-- ========================= PRODUCT GRID ========================= -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3" id="productGrid">

        <?php if (empty($products)): ?>
            <!-- Empty State -->
            <div class="col-12">
                <div class="empty-state">
                    <i class="bi bi-box-seam"></i>
                    <h5>Tiada Produk</h5>
                    <p>Tiada produk ditemui dalam sistem.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($products as $item):
            $stok = (int)$item['stok_semasa'];

            $gambar = $item['gambar_produk'] ?? null;
            $has_image = (!empty($gambar) && file_exists($gambar));
        ?>
        <div class="col product-item"
             data-name="<?php echo htmlspecialchars(strtolower($item['perihal_stok'])); ?>"
             data-kod="<?php echo htmlspecialchars(strtolower($item['no_kod'])); ?>"
             data-kategori="<?php echo htmlspecialchars($item['kategori'] ?? ''); ?>"
             data-subkategori="<?php echo htmlspecialchars($item['subkategori'] ?? ''); ?>"
             data-stock="<?php echo $stok; ?>">

            <div class="card product-card h-100 shadow-sm"
                 id="card-<?php echo htmlspecialchars($item['no_kod']); ?>">

                <!-- Product Image -->
                <?php if ($has_image): ?>
                    <img src="<?php echo htmlspecialchars($gambar); ?>"
                         class="card-img-top"
                         alt="<?php echo htmlspecialchars($item['perihal_stok']); ?>">
                <?php else: ?>
                    <div class="product-img-placeholder">
                        <i class="bi bi-image"></i>
                    </div>
                <?php endif; ?>

                <!-- Card Body -->
                <div class="card-body d-flex flex-column p-3">

                    <!-- Category Badge -->
                    <?php if (!empty($item['kategori'])): ?>
                        <span class="badge bg-light text-dark border mb-2" style="width: fit-content; font-size: 0.7rem;">
                            <?php echo htmlspecialchars($item['kategori']);
                            if (!empty($item['subkategori'])) {
                                echo ' <i class="bi bi-chevron-right" style="font-size:0.55rem;"></i> ' . htmlspecialchars($item['subkategori']);
                            } ?>
                        </span>
                    <?php endif; ?>

                    <!-- Product Name -->
                    <h6 class="product-name mb-2">
                        <?php echo htmlspecialchars($item['perihal_stok']); ?>
                    </h6>

                    <!-- Spacer to push controls to bottom -->
                    <div class="mt-auto text-center">
                        <!-- Quantity Stepper -->
                        <div class="input-group input-group-sm qty-stepper mb-2">
                            <button class="btn btn-outline-secondary qty-minus" type="button"
                                    data-kod="<?php echo htmlspecialchars($item['no_kod']); ?>">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" class="form-control qty-input"
                                   id="qty-<?php echo htmlspecialchars($item['no_kod']); ?>"
                                   value="1" min="1"
                                   data-max="<?php echo $stok; ?>">
                            <button class="btn btn-outline-secondary qty-plus" type="button"
                                    data-kod="<?php echo htmlspecialchars($item['no_kod']); ?>">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>

                        <!-- Add to Cart Button -->
                        <button class="btn btn-sm btn-add-cart" style="min-width: 150px;"
                                data-kod="<?php echo htmlspecialchars($item['no_kod']); ?>"
                                data-name="<?php echo htmlspecialchars($item['perihal_stok']); ?>"
                                data-stock="<?php echo $stok; ?>">
                            <i class="bi bi-cart-plus me-1"></i>Tambah
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    </div>

    <!-- No Results (hidden by default, shown by JS filter) -->
    <div class="empty-state d-none" id="noResults">
        <i class="bi bi-search"></i>
        <h5>Tiada Padanan</h5>
        <p>Tiada produk yang sepadan dengan carian anda.</p>
    </div>

</div>

<!-- ========================= STICKY CART BAR ========================= -->
<div class="cart-bar hidden <?php echo (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) ? 'has-sidebar' : ''; ?>" id="cartBar">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <span class="cart-count-badge" id="cartBarCount">0</span>
            <div>
                <strong id="cartBarLabel">0 item dipilih</strong>
                <br>
                <small class="text-muted" id="cartBarItems">Tiada item</small>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" id="clearCartBtn">
                <i class="bi bi-trash me-1"></i>Kosongkan
            </button>
            <a href="kewps8_form.php?action=new" class="btn btn-primary btn-lg" id="proceedBtn">
                Teruskan ke Borang <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</div>

<!-- ========================= CART PREVIEW MODAL ========================= -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cartModalLabel">
                    <i class="bi bi-cart3 me-2"></i>Senarai Pilihan Anda
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="cartModalBody">
                <!-- Filled by JS -->
            </div>
            <div class="modal-footer">
                <a href="kewps8_form.php?action=new" class="btn btn-primary" id="modalProceedBtn">
                    Teruskan ke Borang <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ========================= JAVASCRIPT ========================= -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- Elements ---
    const productGrid = document.getElementById('productGrid');
    const productItems = document.querySelectorAll('.product-item');
    const searchInput = document.getElementById('searchInput');
    const categoryFilters = document.querySelectorAll('#categoryFilters .filter-pill');
    const subPills = document.querySelectorAll('.sub-pill');
    const sortSelect = document.getElementById('sortSelect');
    const productCount = document.getElementById('productCount');
    const noResults = document.getElementById('noResults');

    const cartBar = document.getElementById('cartBar');
    const cartBarCount = document.getElementById('cartBarCount');
    const cartBarLabel = document.getElementById('cartBarLabel');
    const cartBarItems = document.getElementById('cartBarItems');
    const cartBadge = document.getElementById('cartBadge');
    const clearCartBtn = document.getElementById('clearCartBtn');
    const cartPreviewBtn = document.getElementById('cartPreviewBtn');
    const cartModalBody = document.getElementById('cartModalBody');

    let activeKategories = new Set();
    let activeSubkategories = new Set();

    // ==========================================
    // 1. CATEGORY FILTER (Main categories - multi-select)
    // ==========================================
    categoryFilters.forEach(pill => {
        pill.addEventListener('click', function() {
            const kategori = this.dataset.kategori;
            const semuaBtn = document.querySelector('#categoryFilters .filter-pill[data-kategori=""]');

            if (kategori === '') {
                // "Semua" clicked - clear all
                activeKategories.clear();
                categoryFilters.forEach(p => p.classList.remove('active'));
                semuaBtn.classList.add('active');
            } else {
                // Toggle this category
                if (activeKategories.has(kategori)) {
                    activeKategories.delete(kategori);
                } else {
                    activeKategories.add(kategori);
                }
                semuaBtn.classList.remove('active');
                this.classList.toggle('active');

                // If nothing selected, revert to "Semua"
                if (activeKategories.size === 0) {
                    semuaBtn.classList.add('active');
                }
            }
            filterProducts();
        });
    });

    // ==========================================
    // 1b. SUBCATEGORY FILTER (Independent - multi-select)
    // ==========================================
    subPills.forEach(pill => {
        pill.addEventListener('click', function() {
            const sub = this.dataset.sub;
            const semuaSubBtn = document.querySelector('.sub-pill[data-sub=""]');

            if (sub === '') {
                // "Semua" clicked - clear all
                activeSubkategories.clear();
                subPills.forEach(p => p.classList.remove('active'));
                semuaSubBtn.classList.add('active');
            } else {
                // Toggle this subcategory
                if (activeSubkategories.has(sub)) {
                    activeSubkategories.delete(sub);
                } else {
                    activeSubkategories.add(sub);
                }
                semuaSubBtn.classList.remove('active');
                this.classList.toggle('active');

                // If nothing selected, revert to "Semua"
                if (activeSubkategories.size === 0) {
                    semuaSubBtn.classList.add('active');
                }
            }
            filterProducts();
        });
    });

    // ==========================================
    // 2. SEARCH FILTER
    // ==========================================
    searchInput.addEventListener('input', function() {
        filterProducts();
    });

    // Highlight search text (same pattern as admin_products.php)
    function highlightText(el, searchText) {
        if (!el) return;
        const originalText = el.textContent;
        el.innerHTML = originalText;
        if (searchText && searchText.length > 0) {
            const safeText = searchText.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
            const regex = new RegExp(`(${safeText})`, 'gi');
            el.innerHTML = originalText.replace(regex, '<mark style="background-color: yellow; padding: 0;">$1</mark>');
        }
    }

    function filterProducts() {
        const searchText = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        productItems.forEach(item => {
            const name = item.dataset.name || '';
            const kod = item.dataset.kod || '';
            const kategori = item.dataset.kategori || '';
            const subkategori = item.dataset.subkategori || '';
            const nameEl = item.querySelector('.product-name');

            const matchesSearch = searchText === '' ||
                name.includes(searchText) ||
                kod.includes(searchText);

            const matchesKategori = activeKategories.size === 0 ||
                activeKategories.has(kategori);

            const matchesSubkategori = activeSubkategories.size === 0 ||
                activeSubkategories.has(subkategori);

            if (matchesSearch && matchesKategori && matchesSubkategori) {
                item.style.display = '';
                visibleCount++;
                highlightText(nameEl, searchText);
            } else {
                item.style.display = 'none';
                highlightText(nameEl, '');
            }
        });

        // Update count
        productCount.textContent = 'Menunjukkan ' + visibleCount + ' produk';

        // Show/hide no results
        if (visibleCount === 0 && productItems.length > 0) {
            noResults.classList.remove('d-none');
        } else {
            noResults.classList.add('d-none');
        }
    }

    // ==========================================
    // 3. SORT
    // ==========================================
    sortSelect.addEventListener('change', function() {
        const items = Array.from(productItems);
        const sortBy = this.value;

        items.sort((a, b) => {
            if (sortBy === 'name-asc') return a.dataset.name.localeCompare(b.dataset.name);
            if (sortBy === 'name-desc') return b.dataset.name.localeCompare(a.dataset.name);
            return 0;
        });

        items.forEach(item => productGrid.appendChild(item));
    });

    // ==========================================
    // 4. QUANTITY STEPPER (+/- buttons)
    // ==========================================
    document.querySelectorAll('.qty-minus').forEach(btn => {
        btn.addEventListener('click', function() {
            const kod = this.dataset.kod;
            const input = document.getElementById('qty-' + kod);
            let val = parseInt(input.value) || 1;
            if (val > 1) input.value = val - 1;
        });
    });

    document.querySelectorAll('.qty-plus').forEach(btn => {
        btn.addEventListener('click', function() {
            const kod = this.dataset.kod;
            const input = document.getElementById('qty-' + kod);
            let val = parseInt(input.value) || 1;
            input.value = val + 1;
        });
    });

    // Allow free typing in quantity (no capping on input)
    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('change', function() {
            let val = parseInt(this.value) || 1;
            if (val < 1) this.value = 1;
        });
    });

    // ==========================================
    // 5. ADD TO CART (AJAX) - with stock popup validation
    // ==========================================
    document.querySelectorAll('.btn-add-cart').forEach(btn => {
        btn.addEventListener('click', function() {
            const kod = this.dataset.kod;
            const name = this.dataset.name;
            const stock = parseInt(this.dataset.stock) || 0;
            const qty = parseInt(document.getElementById('qty-' + kod).value) || 1;
            const card = document.getElementById('card-' + kod);
            const buttonRef = this;

            // --- Stock validation via popup ---
            if (stock === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Stok Tidak Tersedia',
                    html: '<strong>' + name + '</strong> tiada stok pada masa ini.<br><small class="text-muted">Sila hubungi <strong>Unit Teknologi Maklumat</strong> untuk maklumat lanjut.</small>',
                    confirmButtonText: 'Faham'
                });
                return;
            }

            if (qty > stock) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Melebihi Stok',
                    html: 'Stok tersedia untuk <strong>' + name + '</strong> hanya <strong>' + stock + ' unit</strong>.<br>Kuantiti anda telah diselaraskan.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    document.getElementById('qty-' + kod).value = stock;
                });
                return;
            }

            // --- Proceed to add ---
            buttonRef.disabled = true;
            const originalHtml = buttonRef.innerHTML;
            buttonRef.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch('kewps8_cart_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'add',
                    no_kod: kod,
                    kuantiti: qty,
                    perihal_stok: name,
                    stok_semasa: stock,
                    catatan: '',
                    jawatan: ''
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Visual feedback - card pulse + outline
                    card.classList.add('just-added', 'in-cart');
                    setTimeout(() => card.classList.remove('just-added'), 300);

                    // Change button to "added" state briefly
                    buttonRef.innerHTML = '<i class="bi bi-check-lg me-1"></i>Ditambah!';
                    buttonRef.classList.remove('btn-add-cart');
                    buttonRef.classList.add('btn-success');

                    setTimeout(() => {
                        buttonRef.innerHTML = originalHtml;
                        buttonRef.classList.remove('btn-success');
                        buttonRef.classList.add('btn-add-cart');
                        buttonRef.disabled = false;
                    }, 1200);

                    // Update cart bar
                    updateCartBar();

                } else {
                    Swal.fire('Ralat', data.message || 'Gagal menambah item.', 'error');
                    buttonRef.innerHTML = originalHtml;
                    buttonRef.disabled = false;
                }
            })
            .catch(error => {
                Swal.fire('Ralat', 'Gagal menghubungi server.', 'error');
                buttonRef.innerHTML = originalHtml;
                buttonRef.disabled = false;
            });
        });
    });

    // ==========================================
    // 6. CART BAR - Update from session
    // ==========================================
    function updateCartBar() {
        fetch('kewps8_cart_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'get' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.cart) {
                const items = Object.values(data.cart);
                const count = items.length;

                if (count > 0) {
                    // Show cart bar
                    cartBar.classList.remove('hidden');

                    // Update count
                    cartBarCount.textContent = count;
                    cartBarLabel.textContent = count + ' item dipilih';
                    cartBadge.textContent = count;
                    cartBadge.classList.remove('d-none');

                    // Show first few item names
                    const names = items.map(i => i.perihal_stok).slice(0, 3);
                    let summary = names.join(', ');
                    if (count > 3) summary += ' +' + (count - 3) + ' lagi';
                    cartBarItems.textContent = summary;

                    // Update card outlines for items in cart
                    document.querySelectorAll('.product-card').forEach(c => c.classList.remove('in-cart'));
                    items.forEach(item => {
                        const c = document.getElementById('card-' + item.no_kod);
                        if (c) c.classList.add('in-cart');
                    });

                } else {
                    // Hide cart bar
                    cartBar.classList.add('hidden');
                    cartBadge.classList.add('d-none');
                    document.querySelectorAll('.product-card').forEach(c => c.classList.remove('in-cart'));
                }
            }
        });
    }

    // ==========================================
    // 7. CLEAR CART
    // ==========================================
    clearCartBtn.addEventListener('click', function() {
        Swal.fire({
            title: 'Kosongkan Senarai?',
            text: 'Semua item yang dipilih akan dibuang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, kosongkan'
        }).then(result => {
            if (result.isConfirmed) {
                fetch('kewps8_cart_ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'clear' })
                })
                .then(response => response.json())
                .then(data => {
                    updateCartBar();
                    Swal.fire({
                        title: 'Dikosongkan',
                        text: 'Senarai telah dikosongkan.',
                        icon: 'success',
                        timer: 1200,
                        showConfirmButton: false
                    });
                });
            }
        });
    });

    // ==========================================
    // 8. CART PREVIEW MODAL
    // ==========================================
    cartPreviewBtn.addEventListener('click', function() {
        fetch('kewps8_cart_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'get' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.cart) {
                const items = Object.values(data.cart);

                if (items.length === 0) {
                    cartModalBody.innerHTML = `
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-cart fs-1 d-block mb-2"></i>
                            Senarai anda kosong. Sila pilih item dahulu.
                        </div>`;
                } else {
                    let html = '<div class="list-group list-group-flush">';
                    items.forEach(item => {
                        html += `
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <strong>${item.perihal_stok}</strong>
                                </div>
                                <span class="badge bg-primary rounded-pill">${item.kuantiti} unit</span>
                            </div>`;
                    });
                    html += '</div>';
                    cartModalBody.innerHTML = html;
                }

                new bootstrap.Modal(document.getElementById('cartModal')).show();
            }
        });
    });

    // ==========================================
    // 9. INIT - Check cart on page load
    // ==========================================
    updateCartBar();

});
</script>

<?php
// Load footer based on role
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    require 'admin_footer.php';
} else {
    require 'staff_footer.php';
}
?>
