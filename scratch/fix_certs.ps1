$profilePath = "profile.html"
if (Test-Path $profilePath) {
    $content = Get-Content $profilePath -Raw

    # Restore the loader script if missing (it should be there, but we might have made a mess)
    # Actually, let's just redo the mapping.

    for ($i = 1; $i -le 9; $i++) {
        $num = $i.ToString("0000")
        $filePath = "c:\xampp\htdocs\InfinityComputer\images\certificates\CERTIFICATE INFINITY COMPUTER_page-$num.jpg"
        Write-Host "Processing $filePath (RAW)..."
        if (Test-Path $filePath) {
            $b64 = [Convert]::ToBase64String([IO.File]::ReadAllBytes($filePath))
            
            # Find the line for this cert
            # Note: The previous script changed 'src="..."' to 'src="" data-src="..."'
            # We will look for id="certX" and data-src="..." or src="..."
            $regex = 'id="cert' + $i + '" [^>]+'
            $replacement = 'id="cert' + $i + '" src="" data-src="' + $b64 + '"'
            $content = $content -replace $regex, $replacement
        }
    }

    for ($i = 1; $i -le 5; $i++) {
        $idNum = $i + 9
        $num = $i.ToString("0000")
        $filePath = "c:\xampp\htdocs\InfinityComputer\images\certificates\CERTIFICATE INFINITY COMPUTER 2_page-$num.jpg"
        Write-Host "Processing $filePath (RAW)..."
        if (Test-Path $filePath) {
            $b64 = [Convert]::ToBase64String([IO.File]::ReadAllBytes($filePath))
            $regex = 'id="cert' + $idNum + '" [^>]+'
            $replacement = 'id="cert' + $idNum + '" src="" data-src="' + $b64 + '"'
            $content = $content -replace $regex, $replacement
        }
    }

    $content | Set-Content $profilePath
    Write-Host "Successfully updated $profilePath with raw images"
} else {
    Write-Host "Could not find $profilePath"
}
