<?php
session_start();
require 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['ID_staf'])) {
    header("Location: index.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password === '' || $confirm_password === '') {
        $message = "Please fill in both password fields.";
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE staf SET katalaluan = ?, is_first_login = 0 WHERE ID_staf = ?");
        $stmt->bind_param("ss", $new_hash, $_SESSION['ID_staf']);
        if ($stmt->execute()) {
            // Redirect to dashboard after successful password change
            if ($_SESSION['peranan'] === 'Admin') {
                header("Location: dashboard_admin.php");
            } else {
                header("Location: dashboard_staf.php");
            }
            exit;
        } else {
            $message = "Error updating password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
</head>
<body>
    <h2>Change Password</h2>
    <?php if ($message): ?>
        <p style="color:red;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="post">
        <label for="new_password">New password:</label><br>
        <input type="password" name="new_password" id="new_password" required><br><br>

        <label for="confirm_password">Confirm password:</label><br>
        <input type="password" name="confirm_password" id="confirm_password" required><br><br>

        <button type="submit">Update</button>
    </form>
</body>
</html>




