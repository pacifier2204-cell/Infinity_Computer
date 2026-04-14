Add-Type -AssemblyName System.Drawing
function Get-CompressedBase64 {
    param($SourcePath, $Quality = 40)
    try {
        $img = [System.Drawing.Image]::FromFile($SourcePath)
        # Resize if too large (e.g. width > 1200)
        $maxWidth = 1200
        if ($img.Width -gt $maxWidth) {
            $ratio = $maxWidth / $img.Width
            $newWidth = $maxWidth
            $newHeight = [int]($img.Height * $ratio)
            $bmp = New-Object System.Drawing.Bitmap($newWidth, $newHeight)
            $g = [System.Drawing.Graphics]::FromImage($bmp)
            $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
            $g.DrawImage($img, 0, 0, $newWidth, $newHeight)
            $g.Dispose()
            $img.Dispose()
            $img = $bmp
        }
        $ms = New-Object System.IO.MemoryStream
        $encoder = [System.Drawing.Imaging.Encoder]::Quality
        $encoderParams = New-Object System.Drawing.Imaging.EncoderParameters(1)
        $encoderParams.Param[0] = New-Object System.Drawing.Imaging.EncoderParameter($encoder, $Quality)
        $ici = [System.Drawing.Imaging.ImageCodecInfo]::GetImageEncoders() | Where-Object { $_.MimeType -eq "image/jpeg" }
        $img.Save($ms, $ici, $encoderParams)
        $base64 = [Convert]::ToBase64String($ms.ToArray())
        $img.Dispose()
        $ms.Dispose()
        return $base64
    } catch {
        Write-Host "Failed to process $SourcePath : $_"
        return ""
    }
}

$profilePath = "profile.html"
if (Test-Path $profilePath) {
    $content = Get-Content $profilePath -Raw

    for ($i = 1; $i -le 9; $i++) {
        $num = $i.ToString("0000")
        $filePath = "c:\xampp\htdocs\InfinityComputer\images\certificates\CERTIFICATE INFINITY COMPUTER_page-$num.jpg"
        Write-Host "Processing $filePath..."
        $b64 = Get-CompressedBase64 $filePath
        if ($b64 -ne "") {
            $regex = 'id="cert' + $i + '" src="[^"]+"'
            # Escape $ in replacement if any? No, it's just a string in PS double quotes. 
            # But regex replacement uses $ for groups. I'll use literal replacement if possible.
            $target = 'id="cert' + $i + '" src="images/certificates/CERTIFICATE INFINITY COMPUTER_page-' + $num + '.jpg"'
            $replacement = 'id="cert' + $i + '" src="" data-src="' + $b64 + '"'
            $content = $content.Replace($target, $replacement)
        }
    }

    for ($i = 1; $i -le 5; $i++) {
        $idNum = $i + 9
        $num = $i.ToString("0000")
        $filePath = "c:\xampp\htdocs\InfinityComputer\images\certificates\CERTIFICATE INFINITY COMPUTER 2_page-$num.jpg"
        Write-Host "Processing $filePath..."
        $b64 = Get-CompressedBase64 $filePath
        if ($b64 -ne "") {
            $target = 'id="cert' + $idNum + '" src="images/certificates/CERTIFICATE INFINITY COMPUTER 2_page-' + $num + '.jpg"'
            $replacement = 'id="cert' + $idNum + '" src="" data-src="' + $b64 + '"'
            $content = $content.Replace($target, $replacement)
        }
    }

    # Add the loading script before </body>
    if ($content -notlike "*img.src = 'data:image/jpeg;base64,'*") {
        $loaderScript = @"
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const certs = document.querySelectorAll('img[data-src]');
      certs.forEach(img => {
        img.src = 'data:image/jpeg;base64,' + img.getAttribute('data-src');
        img.removeAttribute('data-src');
      });
    });
  </script>
</body>
"@
        $content = $content.Replace('</body>', $loaderScript)
    }

    $content | Set-Content $profilePath
    Write-Host "Successfully updated $profilePath"
} else {
    Write-Host "Could not find $profilePath"
}
