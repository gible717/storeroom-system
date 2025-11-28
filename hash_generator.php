<?php
// hash_generator.php - Generate password hash for testing

$password_to_hash = 'NewPass456';

$correct_hash = password_hash($password_to_hash, PASSWORD_BCRYPT);

echo 'The 100% correct hash for "' . $password_to_hash . '" on your server is:';
echo '<br><br>';
echo '<strong style="font-size: 1.2em; background: #eee; padding: 5px;">' . $correct_hash . '</strong>';
?>