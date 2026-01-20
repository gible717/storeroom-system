<?php
/**
 * breadcrumb.php - MyDS Breadcrumb Navigation Helper
 *
 * Usage:
 * 1. Define $breadcrumbs array before including this file
 * 2. Each item should have 'label' and optionally 'url'
 * 3. The last item (current page) should not have 'url'
 *
 * Example:
 * $breadcrumbs = [
 *     ['label' => 'Dashboard', 'url' => 'admin_dashboard.php'],
 *     ['label' => 'Produk', 'url' => 'admin_products.php'],
 *     ['label' => 'Tambah Produk']  // Current page, no URL
 * ];
 * include 'includes/breadcrumb.php';
 */

if (!isset($breadcrumbs) || empty($breadcrumbs)) {
    return; // Don't render if no breadcrumbs defined
}
?>
<nav class="breadcrumb-nav" aria-label="breadcrumb">
    <ol class="breadcrumb">
        <?php foreach ($breadcrumbs as $index => $crumb): ?>
            <?php $isLast = ($index === count($breadcrumbs) - 1); ?>
            <?php if ($isLast): ?>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo htmlspecialchars($crumb['label']); ?>
                </li>
            <?php else: ?>
                <li class="breadcrumb-item">
                    <a href="<?php echo htmlspecialchars($crumb['url'] ?? '#'); ?>">
                        <?php if ($index === 0): ?>
                            <i class="bi bi-house-door me-1"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($crumb['label']); ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>
