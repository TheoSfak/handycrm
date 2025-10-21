# HandyCRM v1.3.5 Release Package Creator
# This script creates a clean distribution package for new installations

Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "HandyCRM v1.3.5 Release Package" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

$sourceDir = "C:\Users\user\Desktop\handycrm"
$releaseDir = "C:\Users\user\Desktop\handycrm-release-1.3.5"
$zipFile = "C:\Users\user\Desktop\handycrm-v1.3.5.zip"

# Step 1: Clean old release directory if exists
if (Test-Path $releaseDir) {
    Write-Host "Cleaning old release directory..." -ForegroundColor Yellow
    Remove-Item -Path $releaseDir -Recurse -Force
}

# Step 2: Create fresh release directory
Write-Host "Creating release directory..." -ForegroundColor Green
New-Item -ItemType Directory -Path $releaseDir -Force | Out-Null

# Step 3: Define files and folders to include
$includeItems = @(
    "classes",
    "config",
    "controllers",
    "database",
    "lang",
    "migrations",
    "models",
    "public",
    "views",
    "index.php",
    ".htaccess",
    "README.md",
    "CHANGELOG.md",
    "INSTALL.md",
    "LICENSE",
    "VERSION"
)

# Step 4: Copy files
Write-Host "Copying files..." -ForegroundColor Green
foreach ($item in $includeItems) {
    $sourcePath = Join-Path $sourceDir $item
    $destPath = Join-Path $releaseDir $item
    
    if (Test-Path $sourcePath) {
        if ((Get-Item $sourcePath).PSIsContainer) {
            # It's a directory
            Copy-Item -Path $sourcePath -Destination $destPath -Recurse -Force
            Write-Host "  ✓ Copied folder: $item" -ForegroundColor Gray
        } else {
            # It's a file
            Copy-Item -Path $sourcePath -Destination $destPath -Force
            Write-Host "  ✓ Copied file: $item" -ForegroundColor Gray
        }
    } else {
        Write-Host "  ⚠ Not found: $item" -ForegroundColor Yellow
    }
}

# Step 5: Create uploads directory structure
Write-Host "Creating uploads directories..." -ForegroundColor Green
$uploadsDir = Join-Path $releaseDir "uploads"
New-Item -ItemType Directory -Path $uploadsDir -Force | Out-Null
New-Item -ItemType Directory -Path (Join-Path $uploadsDir "projects") -Force | Out-Null
New-Item -ItemType Directory -Path (Join-Path $uploadsDir "tasks") -Force | Out-Null
New-Item -ItemType Directory -Path (Join-Path $uploadsDir "temp") -Force | Out-Null

# Create .gitkeep files to preserve empty directories
New-Item -ItemType File -Path (Join-Path $uploadsDir ".gitkeep") -Force | Out-Null
Write-Host "  ✓ Created uploads structure" -ForegroundColor Gray

# Step 6: Create logs directory
Write-Host "Creating logs directory..." -ForegroundColor Green
$logsDir = Join-Path $releaseDir "logs"
New-Item -ItemType Directory -Path $logsDir -Force | Out-Null
New-Item -ItemType File -Path (Join-Path $logsDir ".gitkeep") -Force | Out-Null
Write-Host "  ✓ Created logs directory" -ForegroundColor Gray

# Step 7: Copy config example (not actual config)
Write-Host "Creating config example..." -ForegroundColor Green
$configPath = Join-Path $releaseDir "config\config.php"
if (Test-Path $configPath) {
    $configExamplePath = Join-Path $releaseDir "config\config.php.example"
    Copy-Item -Path $configPath -Destination $configExamplePath -Force
    Remove-Item -Path $configPath -Force
    Write-Host "  ✓ Created config.php.example" -ForegroundColor Gray
}

# Step 8: Clean development files
Write-Host "Cleaning development files..." -ForegroundColor Green
$cleanPatterns = @(
    "*.log",
    ".DS_Store",
    "Thumbs.db",
    ".env",
    ".env.local",
    "composer.lock",
    "package-lock.json",
    "node_modules",
    ".git",
    ".gitignore",
    ".vscode",
    ".idea"
)

foreach ($pattern in $cleanPatterns) {
    Get-ChildItem -Path $releaseDir -Filter $pattern -Recurse -Force -ErrorAction SilentlyContinue | 
        Remove-Item -Force -Recurse -ErrorAction SilentlyContinue
}
Write-Host "  ✓ Cleaned development files" -ForegroundColor Gray

# Step 9: Create README for release
Write-Host "Creating release README..." -ForegroundColor Green
$releaseReadme = @"
# HandyCRM v1.3.5 - Release Package

Thank you for downloading HandyCRM v1.3.5!

## 📦 Package Contents

This package includes:
- Complete HandyCRM application files
- Database schema and migrations
- Installation guide (INSTALL.md)
- Documentation (README.md)
- Changelog (CHANGELOG.md)

## 🚀 Quick Start

1. **Read INSTALL.md** - Complete installation instructions
2. **Extract files** to your web server directory
3. **Create database** using database/schema.sql
4. **Configure** config/config.php (copy from config.php.example)
5. **Set permissions** on uploads/ directory
6. **Access** via web browser

## 📖 Documentation

- **Installation**: See INSTALL.md for step-by-step guide
- **Features**: See README.md for complete feature list
- **Changelog**: See CHANGELOG.md for version history

## 🔐 Default Login

After installation, use these credentials:
- Username: admin
- Password: admin123

**⚠️ IMPORTANT**: Change the password immediately after first login!

## 🆕 What's New in v1.3.5

### Payment Management
- Summary statistics with grand totals
- Quick date preset buttons
- CSV export functionality
- Bulk payment marking
- Visual progress bars and color coding

### Role-Based Access Control
- 4-tier role system (Admin, Supervisor, Technician, Assistant)
- Dynamic menu based on user role
- Permission guards in controllers

### Bug Fixes
- Fixed duplicate technician cards
- Supervisors included in payment lists
- Correct amount display in bulk payment modal

## 📞 Support

- GitHub: https://github.com/TheoSfak/handycrm
- Email: theodore.sfakianakis@gmail.com
- Issues: https://github.com/TheoSfak/handycrm/issues

## 📄 License

© 2025 Theodore Sfakianakis. All rights reserved.

---

**Version**: 1.3.5
**Release Date**: October 21, 2025
**Build**: Stable
"@

Set-Content -Path (Join-Path $releaseDir "README_RELEASE.txt") -Value $releaseReadme -Encoding UTF8
Write-Host "  ✓ Created release README" -ForegroundColor Gray

# Step 10: Create ZIP archive
Write-Host ""
Write-Host "Creating ZIP archive..." -ForegroundColor Green

if (Test-Path $zipFile) {
    Remove-Item -Path $zipFile -Force
}

Compress-Archive -Path "$releaseDir\*" -DestinationPath $zipFile -CompressionLevel Optimal

$zipSize = (Get-Item $zipFile).Length / 1MB
Write-Host "  ✓ Created: $zipFile" -ForegroundColor Gray
Write-Host "  ✓ Size: $([math]::Round($zipSize, 2)) MB" -ForegroundColor Gray

# Step 11: Calculate file counts
$fileCount = (Get-ChildItem -Path $releaseDir -Recurse -File).Count
$folderCount = (Get-ChildItem -Path $releaseDir -Recurse -Directory).Count

# Step 12: Summary
Write-Host ""
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "Release Package Created Successfully!" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Package Location:" -ForegroundColor White
Write-Host "  $zipFile" -ForegroundColor Yellow
Write-Host ""
Write-Host "Statistics:" -ForegroundColor White
Write-Host "  Files: $fileCount" -ForegroundColor Gray
Write-Host "  Folders: $folderCount" -ForegroundColor Gray
Write-Host "  Size: $([math]::Round($zipSize, 2)) MB" -ForegroundColor Gray
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor White
Write-Host "  1. Upload to GitHub Releases" -ForegroundColor Gray
Write-Host "  2. Tag as v1.3.5" -ForegroundColor Gray
Write-Host "  3. Add release notes from CHANGELOG.md" -ForegroundColor Gray
Write-Host ""
Write-Host "GitHub Release URL:" -ForegroundColor White
Write-Host "  https://github.com/TheoSfak/handycrm/releases/new" -ForegroundColor Cyan
Write-Host ""
