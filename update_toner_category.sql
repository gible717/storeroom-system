-- First, let's see what categories exist
SELECT * FROM kategori;

-- Find products without category (NULL or empty)
SELECT ID_produk, nama_produk, ID_kategori FROM PRODUK WHERE ID_kategori IS NULL OR ID_kategori = '';

-- Update all products to Toner category (replace XX with the actual ID_kategori for 'Toner')
-- First run the SELECT above to find the Toner category ID, then uncomment and run this:
-- UPDATE PRODUK SET ID_kategori = (SELECT ID_kategori FROM kategori WHERE nama_kategori = 'Toner') WHERE ID_kategori IS NULL OR ID_kategori = '';
