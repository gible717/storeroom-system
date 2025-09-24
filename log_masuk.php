<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Log Masuk</title>
</head>
<body>
    <h2>Sistem Pengurusan Stor</h2>
    <?php if (isset($_GET['error'])): ?>
        <p style="color:red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>

    <form method="post" action="proses_log_masuk.php">
        <label>ID Staf:</label><br>
        <input type="text" name="ID_staf" required><br><br>

        <label>Katalaluan:</label><br>
        <input type="password" name="katalaluan" required><br><br>

        <button type="submit">Log Masuk</button>
    </form>
</body>
</html>
