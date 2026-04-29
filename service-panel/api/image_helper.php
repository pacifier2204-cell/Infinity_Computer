<?php
/**
 * Image processing helper for Infinity Computer
 * Handles watermarking and timestamping
 */

function processAndSaveImage($file, $target_dir, $watermark_text = "Infinity Computer") {
    if (!isset($file) || $file['error'] !== 0) {
        return null;
    }

    $type = mime_content_type($file['tmp_name']);
    if ($type !== 'image/jpeg' && $type !== 'image/png') {
        return null;
    }

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $filename = time() . '_' . rand(1000, 9999) . '.jpg';
    $target_file = $target_dir . $filename;
    $source = $file['tmp_name'];

    // If GD is missing, just move the file
    if (!function_exists('imagecreatefromjpeg')) {
        move_uploaded_file($source, $target_file);
        return $filename;
    }

    $image = ($type === 'image/png') ? @imagecreatefrompng($source) : @imagecreatefromjpeg($source);
    if (!$image) {
        move_uploaded_file($source, $target_file);
        return $filename;
    }

    $width = imagesx($image);
    $height = imagesy($image);
    
    // Optional: Resize if too large (e.g., max 2000px width)
    $max_dim = 2000;
    if ($width > $max_dim || $height > $max_dim) {
        $ratio = min($max_dim / $width, $max_dim / $height);
        $new_w = floor($width * $ratio);
        $new_h = floor($height * $ratio);
        $tmp = imagecreatetruecolor($new_w, $new_h);
        
        // Preserve transparency for PNG if we were keeping PNG, but we convert to JPG to bake in watermark
        imagecopyresampled($tmp, $image, 0, 0, 0, 0, $new_w, $new_h, $width, $height);
        imagedestroy($image);
        $image = $tmp;
        $width = $new_w;
        $height = $new_h;
    }

    $font_path = 'C:/Windows/Fonts/arial.ttf';
    $use_ttf = file_exists($font_path) && function_exists('imagettftext');

    // Config Colors
    $white_fill = imagecolorallocatealpha($image, 255, 255, 255, 60); // ~50% transparent
    $black_stroke = imagecolorallocatealpha($image, 0, 0, 0, 70);    // ~45% transparent stroke
    $white_solid = imagecolorallocate($image, 255, 255, 255);

    // 1. Watermark (Center)
    $watermark_text = "Infinity Computer";
    if ($use_ttf) {
        $fsize = $width / 10;
        $angle = 25;
        $bbox = imagettfbbox($fsize, $angle, $font_path, $watermark_text);
        $tw = $bbox[2] - $bbox[6]; $th = $bbox[3] - $bbox[7];
        $x = ($width / 2) - ($tw / 2);
        $y = ($height / 2) + ($th / 2);
        
        // High Contrast Border
        $offset = max(1, $width / 600);
        imagettftext($image, $fsize, $angle, $x+$offset, $y+$offset, $black_stroke, $font_path, $watermark_text);
        imagettftext($image, $fsize, $angle, $x-$offset, $y-$offset, $black_stroke, $font_path, $watermark_text);
        imagettftext($image, $fsize, $angle, $x+$offset, $y-$offset, $black_stroke, $font_path, $watermark_text);
        imagettftext($image, $fsize, $angle, $x-$offset, $y+$offset, $black_stroke, $font_path, $watermark_text);
        
        // Main Text (Transparent)
        imagettftext($image, $fsize, $angle, $x, $y, $white_fill, $font_path, $watermark_text);
    } else {
        $fsize = 5;
        $tw = imagefontwidth($fsize) * strlen($watermark_text);
        $x = ($width - $tw) / 2; $y = $height / 2;
        imagestring($image, $fsize, $x+1, $y+1, $black_stroke, $watermark_text);
        imagestring($image, $fsize, $x, $y, $white_fill, $watermark_text);
    }

    // 2. Timestamp (Bottom-Right)
    $ts = date("Y-m-d H:i:s");
    if ($use_ttf) {
        $tsize = max(18, $width / 30); // Increased size
        $bbox = imagettfbbox($tsize, 0, $font_path, $ts);
        $tw = $bbox[2] - $bbox[0]; $th = $bbox[1] - $bbox[7];
        $tx = $width - $tw - 30; $ty = $height - 30;
        
        // Solid dark background for absolute visibility
        imagefilledrectangle($image, $tx - 15, $ty - $th - 15, $width, $height, imagecolorallocatealpha($image, 0, 0, 0, 50));
        imagettftext($image, $tsize, 0, $tx, $ty, $white_solid, $font_path, $ts);
    } else {
        $tw = imagefontwidth(3) * strlen($ts);
        $th = imagefontheight(3);
        $tx = $width - $tw - 15; $ty = $height - $th - 15;
        imagefilledrectangle($image, $tx - 5, $ty - 5, $width, $height, $black_stroke);
        imagestring($image, 3, $tx, $ty, $ts, $white_solid);
    }

    // 3. Save as JPEG with 80% quality
    imagejpeg($image, $target_file, 80);
    imagedestroy($image);

    return $filename;
}
