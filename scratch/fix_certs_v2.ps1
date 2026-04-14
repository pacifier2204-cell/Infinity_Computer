$profilePath = "profile.html"
if (Test-Path $profilePath) {
    $content = Get-Content $profilePath -Raw

    for ($i = 1; $i -le 9; $i++) {
        $num = $i.ToString("0000")
        $filePath = "c:\xampp\htdocs\InfinityComputer\images\certificates\CERTIFICATE INFINITY COMPUTER_page-$num.jpg"
        Write-Host "Processing $filePath (RAW + ALT)..."
        if (Test-Path $filePath) {
            $b64 = [Convert]::ToBase64String([IO.File]::ReadAllBytes($filePath))
            $regex = 'id="cert' + $i + '" [^>]+'
            $replacement = 'id="cert' + $i + '" src="" data-src="' + $b64 + '" alt="Certificate"'
            $content = $content -replace $regex, $replacement
        }
    }

    for ($i = 1; $i -le 5; $i++) {
        $idNum = $i + 9
        $num = $i.ToString("0000")
        $filePath = "c:\xampp\htdocs\InfinityComputer\images\certificates\CERTIFICATE INFINITY COMPUTER 2_page-$num.jpg"
        Write-Host "Processing $filePath (RAW + ALT)..."
        if (Test-Path $filePath) {
            $b64 = [Convert]::ToBase64String([IO.File]::ReadAllBytes($filePath))
            $regex = 'id="cert' + $idNum + '" [^>]+'
            $replacement = 'id="cert' + $idNum + '" src="" data-src="' + $b64 + '" alt="Certificate"'
            $content = $content -replace $regex, $replacement
        }
    }

    $content | Set-Content $profilePath
    Write-Host "Successfully updated $profilePath with raw images and alt tags"
} else {
    Write-Host "Could not find $profilePath"
}
