<?php
/**
 * image_optimizer.php - Server-side image optimization for product photos.
 * Resizes to max width and converts to WebP for storage efficiency.
 * Falls back gracefully if GD extension is not available.
 */

function optimizeProductImage($source_path, $max_width = 800, $quality = 85) {
    // Graceful fallback if GD is not available
    if (!extension_loaded('gd')) {
        return $source_path;
    }

    // Check WebP support in GD
    if (!function_exists('imagewebp')) {
        return $source_path;
    }

    $image_info = @getimagesize($source_path);
    if ($image_info === false) {
        return $source_path;
    }

    $mime = $image_info['mime'];
    $orig_width = $image_info[0];
    $orig_height = $image_info[1];

    // Load image based on type
    switch ($mime) {
        case 'image/jpeg':
            $source = @imagecreatefromjpeg($source_path);
            break;
        case 'image/png':
            $source = @imagecreatefrompng($source_path);
            break;
        case 'image/webp':
            $source = @imagecreatefromwebp($source_path);
            break;
        default:
            return $source_path;
    }

    if (!$source) {
        return $source_path;
    }

    // Calculate new dimensions (maintain aspect ratio)
    if ($orig_width > $max_width) {
        $ratio = $max_width / $orig_width;
        $new_width = $max_width;
        $new_height = (int)round($orig_height * $ratio);
    } else {
        // Already small enough, still convert to WebP for savings
        $new_width = $orig_width;
        $new_height = $orig_height;
    }

    // Create resized canvas
    $resized = imagecreatetruecolor($new_width, $new_height);

    // Preserve transparency
    imagealphablending($resized, false);
    imagesavealpha($resized, true);

    imagecopyresampled($resized, $source, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height);
    imagedestroy($source);

    // Save as WebP
    $webp_path = preg_replace('/\.(jpe?g|png|webp)$/i', '.webp', $source_path);
    $success = @imagewebp($resized, $webp_path, $quality);
    imagedestroy($resized);

    if ($success) {
        // Delete original file if it's different from WebP output
        if ($webp_path !== $source_path && file_exists($source_path)) {
            @unlink($source_path);
        }
        return $webp_path;
    }

    // WebP save failed — return original untouched
    return $source_path;
}
