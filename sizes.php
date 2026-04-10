<?php
$files = glob('images/Certificates/*.jpg');
foreach($files as $f){
    $exif = @exif_read_data($f);
    $ort = isset($exif['Orientation']) ? $exif['Orientation'] : 'None';
    echo basename($f).': '.$ort.PHP_EOL;
}
?>
