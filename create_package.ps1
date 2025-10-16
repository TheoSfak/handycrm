# HandyCRM v1.2.0 - Installation Package Creator (Windows)
# Creates a clean distribution package without development files

$VERSION = "1.2.0"
$PACKAGE_NAME = "HandyCRM-v$VERSION"
$BUILD_DIR = "build"
$DIST_DIR = "$BUILD_DIR\$PACKAGE_NAME"

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "HandyCRM v$VERSION - Package Creator" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

# Clean previous builds
Write-Host "`nCleaning previous builds..." -ForegroundColor Yellow
if (Test-Path $BUILD_DIR) {
    Remove-Item -Recurse -Force $BUILD_DIR
}
New-Item -ItemType Directory -Path $DIST_DIR -Force | Out-Null

# Copy application directories
Write-Host "Copying application files..." -ForegroundColor Yellow
$dirs = @("api", "backups", "classes", "config", "controllers", "database", 
          "helpers", "languages", "migrations", "models", "public", 
          "scripts", "uploads", "views")

foreach ($dir in $dirs) {
    if (Test-Path $dir) {
        Copy-Item -Recurse -Force $dir "$DIST_DIR\"
        Write-Host "  ✓ Copied $dir" -ForegroundColor Green
    }
}

# Copy root files
Write-Host "`nCopying root files..." -ForegroundColor Yellow
$files = @(".htaccess", "index.php", "install.php", "router.php", 
           "VERSION", "LICENSE", "README.md", "CHANGELOG.md", 
           "RELEASE_NOTES_v1.2.0.md")

foreach ($file in $files) {
    if (Test-Path $file) {
        Copy-Item -Force $file "$DIST_DIR\"
        Write-Host "  ✓ Copied $file" -ForegroundColor Green
    }
}

# Create required empty directories
Write-Host "`nCreating required empty directories..." -ForegroundColor Yellow
New-Item -ItemType Directory -Path "$DIST_DIR\uploads\projects" -Force | Out-Null
New-Item -ItemType Directory -Path "$DIST_DIR\backups" -Force | Out-Null
New-Item -ItemType File -Path "$DIST_DIR\uploads\projects\.gitkeep" -Force | Out-Null
New-Item -ItemType File -Path "$DIST_DIR\backups\.gitkeep" -Force | Out-Null

# Remove development/test files
Write-Host "`nRemoving development files..." -ForegroundColor Yellow
if (Test-Path "$DIST_DIR\test_utf8.php") { 
    Remove-Item "$DIST_DIR\test_utf8.php" 
    Write-Host "  ✓ Removed test_utf8.php" -ForegroundColor Green
}
if (Test-Path "$DIST_DIR\TESTING_GUIDE.md") { 
    Remove-Item "$DIST_DIR\TESTING_GUIDE.md" 
    Write-Host "  ✓ Removed TESTING_GUIDE.md" -ForegroundColor Green
}
if (Test-Path "$DIST_DIR\.git") { 
    Remove-Item -Recurse -Force "$DIST_DIR\.git" 
    Write-Host "  ✓ Removed .git" -ForegroundColor Green
}
if (Test-Path "$DIST_DIR\.gitignore") { 
    Remove-Item "$DIST_DIR\.gitignore" 
    Write-Host "  ✓ Removed .gitignore" -ForegroundColor Green
}

# Create ZIP archive
Write-Host "`nCreating ZIP archive..." -ForegroundColor Yellow
$zipPath = "$BUILD_DIR\$PACKAGE_NAME.zip"
Compress-Archive -Path $DIST_DIR -DestinationPath $zipPath -Force
$zipSize = (Get-Item $zipPath).Length / 1MB
Write-Host "  ✓ Created $PACKAGE_NAME.zip" -ForegroundColor Green

Write-Host "`n==========================================" -ForegroundColor Cyan
Write-Host "Package created successfully!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "`nLocation: $zipPath" -ForegroundColor White
Write-Host "Size: $([math]::Round($zipSize, 2)) MB" -ForegroundColor White
Write-Host "`n==========================================" -ForegroundColor Cyan
Write-Host "`nNext steps:" -ForegroundColor Yellow
Write-Host "1. Test installation from ZIP" -ForegroundColor White
Write-Host "2. Create GitHub release at:" -ForegroundColor White
Write-Host "   https://github.com/TheoSfak/handycrm/releases/new" -ForegroundColor Cyan
Write-Host "3. Upload $PACKAGE_NAME.zip as release asset" -ForegroundColor White
Write-Host "4. Tag: v$VERSION" -ForegroundColor White
Write-Host "5. Copy contents from RELEASE_NOTES_v1.2.0.md to description" -ForegroundColor White
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "`nPress any key to open GitHub releases page..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
Start-Process "https://github.com/TheoSfak/handycrm/releases/new?tag=v$VERSION"
