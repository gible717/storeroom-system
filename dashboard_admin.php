<?php
session_start();
if (!isset($_SESSION['ID_staf']) || $_SESSION['peranan'] !== 'Admin') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>
    <h2>Welcome Admin, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h2>
    <p>This is the admin dashboard.</p>
    <a href="logout.php">Logout</a>
</body>
</html>

