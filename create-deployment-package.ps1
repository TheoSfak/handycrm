#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Creates a clean deployment package of HandyCRM
    
.DESCRIPTION
    This script creates a deployment-ready ZIP package of HandyCRM by:
    - Using git archive to export clean code (no .git, no ignored files)
    - Excluding development-only files
    - Including all necessary language files, classes, and database schema
    - Properly excluding config.php so installer runs on fresh installations
    
.PARAMETER Version
    Version number for the package (e.g., "1.0.4")
    
.EXAMPLE
    .\create-deployment-package.ps1 -Version "1.0.4"
    
.NOTES
    Author: Theodore Sfakianakis
    Email: theodore.sfakianakis@gmail.com
    Date: October 10, 2025
#>

param(
    [Parameter(Mandatory=$true)]
    [string]$Version
)

$ErrorActionPreference = "Stop"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host " HandyCRM Deployment Package Creator" -ForegroundColor Cyan
Write-Host " Version: $Version" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# Check if git is installed
try {
    $gitPath = & "C:\Program Files\Git\bin\git.exe" --version
    Write-Host "✓ Git detected: $gitPath" -ForegroundColor Green
} catch {
    Write-Host "✗ Git not found. Please install Git first." -ForegroundColor Red
    exit 1
}

# Check if we're in a git repository
$isGitRepo = Test-Path ".git"
if (-not $isGitRepo) {
    Write-Host "✗ Not in a git repository!" -ForegroundColor Red
    exit 1
}
Write-Host "✓ Git repository detected" -ForegroundColor Green

# Get current branch
$currentBranch = & "C:\Program Files\Git\bin\git.exe" rev-parse --abbrev-ref HEAD
Write-Host "✓ Current branch: $currentBranch" -ForegroundColor Green

# Check for uncommitted changes
$statusOutput = & "C:\Program Files\Git\bin\git.exe" status --porcelain
if ($statusOutput) {
    Write-Host "`n⚠ Warning: You have uncommitted changes:" -ForegroundColor Yellow
    Write-Host $statusOutput -ForegroundColor Yellow
    $response = Read-Host "`nContinue anyway? (y/n)"
    if ($response -ne "y") {
        Write-Host "Aborted by user." -ForegroundColor Red
        exit 1
    }
}

# Create output directory
$outputDir = ".\deployment-packages"
if (-not (Test-Path $outputDir)) {
    New-Item -ItemType Directory -Path $outputDir | Out-Null
    Write-Host "✓ Created output directory: $outputDir" -ForegroundColor Green
}

# Package filename
$packageName = "handycrm-v$Version-deploy"
$packagePath = Join-Path $outputDir "$packageName.zip"
$tempDir = Join-Path $outputDir "temp-$packageName"

Write-Host "`n📦 Creating deployment package..." -ForegroundColor Cyan

# Remove old package if exists
if (Test-Path $packagePath) {
    Remove-Item $packagePath -Force
    Write-Host "  • Removed old package" -ForegroundColor Gray
}

# Remove temp directory if exists
if (Test-Path $tempDir) {
    Remove-Item $tempDir -Recurse -Force
    Write-Host "  • Cleaned temp directory" -ForegroundColor Gray
}

# Create temp directory
New-Item -ItemType Directory -Path $tempDir | Out-Null

# Export files using git archive (this respects .gitignore)
Write-Host "`n📋 Exporting files from git..." -ForegroundColor Cyan
& "C:\Program Files\Git\bin\git.exe" archive HEAD | tar -x -C $tempDir
Write-Host "  ✓ Git archive extracted" -ForegroundColor Green

# Create necessary directories that might not be in git
$requiredDirs = @(
    "uploads",
    "uploads/customers",
    "uploads/projects",
    "uploads/temp"
)

Write-Host "`n📁 Creating required directories..." -ForegroundColor Cyan
foreach ($dir in $requiredDirs) {
    $fullPath = Join-Path $tempDir $dir
    if (-not (Test-Path $fullPath)) {
        New-Item -ItemType Directory -Path $fullPath | Out-Null
        
        # Add .htaccess for security
        $htaccessContent = @"
# Prevent direct access to uploaded files
<Files "*">
    Order Deny,Allow
    Deny from all
</Files>

# Allow images to be displayed
<FilesMatch "\.(jpg|jpeg|png|gif|pdf)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>
"@
        Set-Content -Path (Join-Path $fullPath ".htaccess") -Value $htaccessContent
        Write-Host "  ✓ Created: $dir" -ForegroundColor Green
    }
}

# Verify critical files exist
Write-Host "`n🔍 Verifying critical files..." -ForegroundColor Cyan
$criticalFiles = @(
    "install.php",
    "index.php",
    "config/config.example.php",
    "classes/LanguageManager.php",
    "languages/en.json",
    "languages/el.json",
    "database/handycrm.sql"
)

$allFilesExist = $true
foreach ($file in $criticalFiles) {
    $fullPath = Join-Path $tempDir $file
    if (Test-Path $fullPath) {
        Write-Host "  ✓ $file" -ForegroundColor Green
    } else {
        Write-Host "  ✗ MISSING: $file" -ForegroundColor Red
        $allFilesExist = $false
    }
}

if (-not $allFilesExist) {
    Write-Host "`n✗ Critical files are missing! Package creation aborted." -ForegroundColor Red
    Remove-Item $tempDir -Recurse -Force
    exit 1
}

# Verify config.php is NOT included
$configPhpPath = Join-Path $tempDir "config/config.php"
if (Test-Path $configPhpPath) {
    Write-Host "`n⚠ WARNING: config.php found in package - removing it..." -ForegroundColor Yellow
    Remove-Item $configPhpPath -Force
    Write-Host "  ✓ config.php removed (will be created by installer)" -ForegroundColor Green
} else {
    Write-Host "  ✓ config.php correctly excluded" -ForegroundColor Green
}

# Create installation guide
Write-Host "`n📝 Creating installation guide..." -ForegroundColor Cyan
$installGuide = @"
╔══════════════════════════════════════════════════════════════╗
║                 HandyCRM v$Version                          ║
║              Quick Installation Guide                        ║
╚══════════════════════════════════════════════════════════════╝

INSTALLATION STEPS:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. UPLOAD FILES
   • Upload all files to your web server
   • Ensure files are in public_html or www directory
   
2. SET PERMISSIONS
   • uploads/ folder: 755 (drwxr-xr-x)
   • config/ folder: 755 (drwxr-xr-x)
   
3. CREATE DATABASE
   • Create a MySQL database (utf8mb4_unicode_ci)
   • Create a database user with full privileges
   • Note down: database name, username, password
   
4. RUN INSTALLER
   • Visit: http://your-domain.com/handycrm/
   • System will AUTOMATICALLY redirect to installer
   • Enter your database credentials
   • Click "Install"
   
5. LOGIN
   • Default username: admin
   • Default password: admin123
   • CHANGE PASSWORD IMMEDIATELY after first login!

REQUIREMENTS:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✓ PHP 7.4 or higher
✓ MySQL 5.7 or higher (or MariaDB 10.2+)
✓ Apache with mod_rewrite enabled
✓ PDO PHP Extension
✓ JSON PHP Extension
✓ mbstring PHP Extension

FEATURES:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✓ Multi-language support (Greek/English)
✓ Customer management
✓ Project tracking
✓ Invoice & Quote generation
✓ Appointment scheduling
✓ Material inventory
✓ User management
✓ Comprehensive reporting

TROUBLESHOOTING:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Problem: "Database connection failed"
Solution: Check database credentials in installer

Problem: "Cannot access settings page"  
Solution: Ensure LanguageManager files exist in classes/

Problem: "File permission denied"
Solution: Set uploads/ folder to 755 permissions

Problem: "Page not found / 404 errors"
Solution: Enable mod_rewrite in Apache

SUPPORT:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Author: Theodore Sfakianakis
Email: theodore.sfakianakis@gmail.com
GitHub: https://github.com/TheoSfak/handycrm

For detailed documentation, see DEPLOYMENT_README.txt

Copyright © 2025 Theodore Sfakianakis. All rights reserved.
"@

Set-Content -Path (Join-Path $tempDir "INSTALLATION_GUIDE.txt") -Value $installGuide
Write-Host "  ✓ INSTALLATION_GUIDE.txt created" -ForegroundColor Green

# Create package info file
$packageInfo = @"
HandyCRM Deployment Package
═══════════════════════════════════════════════════════════

Version: $Version
Created: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
Branch: $currentBranch
Commit: $(& "C:\Program Files\Git\bin\git.exe" rev-parse --short HEAD)

Package Contents:
─────────────────────────────────────────────────────────

✓ Core application files
✓ Language system (LanguageManager)
✓ Translation files (English, Greek)
✓ Database schema (handycrm.sql)
✓ Installation wizard
✓ Example configuration
✓ Complete MVC structure

Critical Features:
─────────────────────────────────────────────────────────

✓ Automatic installation system
✓ Multi-language support
✓ Secure configuration generation
✓ Database auto-setup
✓ Clean URL routing

Installation:
─────────────────────────────────────────────────────────

1. Upload files to web server
2. Visit: http://your-domain.com/handycrm/
3. Follow automatic installation wizard
4. Login with admin/admin123
5. Change password immediately

Notes:
─────────────────────────────────────────────────────────

• config.php is NOT included (created by installer)
• Default language: Greek (can be changed in Settings)
• SECRET_KEY generated randomly during installation
• Database credentials entered during installation

Author: Theodore Sfakianakis
Email: theodore.sfakianakis@gmail.com
Copyright © 2025 Theodore Sfakianakis. All rights reserved.
"@

Set-Content -Path (Join-Path $tempDir "PACKAGE_INFO.txt") -Value $packageInfo
Write-Host "  ✓ PACKAGE_INFO.txt created" -ForegroundColor Green

# Create the ZIP package
Write-Host "`n📦 Creating ZIP archive..." -ForegroundColor Cyan
Compress-Archive -Path "$tempDir\*" -DestinationPath $packagePath -CompressionLevel Optimal
Write-Host "  ✓ Package created successfully!" -ForegroundColor Green

# Clean up temp directory
Remove-Item $tempDir -Recurse -Force
Write-Host "  ✓ Cleaned up temporary files" -ForegroundColor Green

# Get package size
$packageSize = (Get-Item $packagePath).Length
$packageSizeKB = [math]::Round($packageSize / 1KB, 2)
$packageSizeMB = [math]::Round($packageSize / 1MB, 2)

# Summary
Write-Host "`n╔══════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║               PACKAGE CREATED SUCCESSFULLY! ✓                 ║" -ForegroundColor Green
Write-Host "╚══════════════════════════════════════════════════════════════╝" -ForegroundColor Green

Write-Host "`n📦 Package Details:" -ForegroundColor Cyan
Write-Host "   Filename: $packageName.zip" -ForegroundColor White
Write-Host "   Location: $packagePath" -ForegroundColor White
Write-Host "   Size: $packageSizeKB KB ($packageSizeMB MB)" -ForegroundColor White
Write-Host "   Version: $Version" -ForegroundColor White
Write-Host "   Created: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor White

Write-Host "`n✅ Ready for deployment!" -ForegroundColor Green
Write-Host "`n📋 Next steps:" -ForegroundColor Cyan
Write-Host "   1. Test the package on a clean server" -ForegroundColor Gray
Write-Host "   2. Verify installation wizard works" -ForegroundColor Gray
Write-Host "   3. Check language switching functionality" -ForegroundColor Gray
Write-Host "   4. Confirm Settings page loads without errors" -ForegroundColor Gray
Write-Host "   5. Share with users or upload to releases`n" -ForegroundColor Gray
