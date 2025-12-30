// seed_users.php - Reset and seed default users for testing
<?php
require 'db.php';

// Clear staf table first
$conn->query("TRUNCATE TABLE staf");

// Hash default password (same for both)
$default_pass = password_hash("User123", PASSWORD_DEFAULT);

// Insert Admin
$conn->query("INSERT INTO staf (ID_staf, nama, kata_laluan, is_admin, is_first_login) VALUES
('A001', 'Admin User', '$default_pass', 1, 1)");

// Insert Staf
$conn->query("INSERT INTO staf (ID_staf, nama, kata_laluan, is_admin, is_first_login) VALUES
('S001', 'Staf User', '$default_pass', 0, 1)");

echo "✅ Admin (A001/User123) and Staf (S001/User123) reseeded successfully.";
?>

