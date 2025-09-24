<?php
session_start();
if (!isset($_SESSION['ID_staf']) || $_SESSION['peranan'] !== 'Staf') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staf Dashboard</title>
</head>
<body>
    <h2>Welcome Staf, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h2>
    <p>This is the staf dashboard.</p>
    <a href="logout.php">Logout</a>
</body>
</html>

