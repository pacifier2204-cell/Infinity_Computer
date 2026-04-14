$path = "profile.html"
if (Test-Path $path) {
    $content = Get-Content $path -Raw
    
    # We use a regex to find the script and remove it, being careful about whitespace
    $regex = '(?s)\s*<script>\s*// Double-click to rotate certificates feature.*?console\.log\("Current Adjustments:", rotations\);\s*\}\);\s*\}\);\s*\}\);\s*</script>'
    
    if ($content -match $regex) {
        $content = $content -replace $regex, ""
        $content | Set-Content $path
        Write-Host "Successfully removed the rotation script from $path"
    } else {
        Write-Host "Could not find the rotation script with the expected pattern."
        # Fallback to a simpler search/replace if literal match is needed
    }
} else {
    Write-Host "Could not find $path"
}
