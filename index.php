<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Storage Room</title>
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($_GET['error'])): ?>
        <p style="color:red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <label for="ID_staf">Staff ID:</label><br>
        <input type="text" name="ID_staf" required><br><br>

        <label for="katalaluan">Password:</label><br>
        <input type="password" name="katalaluan" required><br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>

