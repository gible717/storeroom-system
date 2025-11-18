<?php
if (extension_loaded('gd')) {
    echo "GD extension is enabled! ✅<br>";
    echo "Supported image types:<br>";
    if (function_exists('imagecreatefromjpeg')) echo "- JPEG ✅<br>";
    if (function_exists('imagecreatefrompng')) echo "- PNG ✅<br>";
    if (function_exists('imagecreatefromgif')) echo "- GIF ✅<br>";
} else {
    echo "GD extension is NOT enabled! ❌";
}
phpinfo(INFO_MODULES);
?>