# ====================================
# Deploy HandyCRM to 1stop.gr
# ====================================

Write-Host "🚀 Starting deployment to 1stop.gr..." -ForegroundColor Cyan
Write-Host ""

# FTP Configuration
$ftpServer = "ftp://ftp.1stop.gr"
$ftpUsername = "u858321845"
$ftpPassword = Read-Host "Enter FTP Password" -AsSecureString
$BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($ftpPassword)
$ftpPasswordPlain = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)

$localPath = "c:\Users\user\Desktop\handycrm"
$remotePath = "/public_html"

# Files to upload
$filesToUpload = @(
    @{Local="controllers\ProjectController.php"; Remote="/controllers/ProjectController.php"},
    @{Local="controllers\ReportsController.php"; Remote="/controllers/ReportsController.php"},
    @{Local="views\includes\header.php"; Remote="/views/includes/header.php"},
    @{Local="views\dashboard\index.php"; Remote="/views/dashboard/index.php"},
    @{Local="index.php"; Remote="/index.php"},
    @{Local="migrate_remove_invoices.php"; Remote="/migrate_remove_invoices.php"}
)

# Files to delete
$filesToDelete = @(
    "/controllers/InvoiceController.php",
    "/models/Invoice.php",
    "/views/invoices/index.php",
    "/views/invoices/create.php",
    "/views/invoices/edit.php",
    "/views/invoices/view.php"
)

# Function to upload file via FTP
function Upload-File {
    param($localFile, $remoteFile)
    
    try {
        $fullLocalPath = Join-Path $localPath $localFile
        $fullRemotePath = "$ftpServer$remotePath$remoteFile"
        
        Write-Host "📤 Uploading: $localFile" -ForegroundColor Yellow
        
        $webclient = New-Object System.Net.WebClient
        $webclient.Credentials = New-Object System.Net.NetworkCredential($ftpUsername, $ftpPasswordPlain)
        $webclient.UploadFile($fullRemotePath, $fullLocalPath)
        
        Write-Host "   ✅ Success: $remoteFile" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host "   ❌ Failed: $remoteFile - $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# Function to delete file via FTP
function Delete-FtpFile {
    param($remoteFile)
    
    try {
        $fullRemotePath = "$ftpServer$remotePath$remoteFile"
        
        Write-Host "🗑️  Deleting: $remoteFile" -ForegroundColor Yellow
        
        $ftpRequest = [System.Net.FtpWebRequest]::Create($fullRemotePath)
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUsername, $ftpPasswordPlain)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
        
        $response = $ftpRequest.GetResponse()
        $response.Close()
        
        Write-Host "   ✅ Deleted: $remoteFile" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host "   ⚠️  Could not delete: $remoteFile (may not exist)" -ForegroundColor DarkYellow
        return $false
    }
}

# Step 1: Upload files
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "STEP 1: Uploading updated files" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$uploadCount = 0
foreach ($file in $filesToUpload) {
    if (Upload-File -localFile $file.Local -remoteFile $file.Remote) {
        $uploadCount++
    }
    Start-Sleep -Milliseconds 500
}

Write-Host ""
Write-Host "✅ Uploaded $uploadCount of $($filesToUpload.Count) files" -ForegroundColor Green

# Step 2: Delete old invoice files
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "STEP 2: Deleting invoice module files" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$deleteCount = 0
foreach ($file in $filesToDelete) {
    if (Delete-FtpFile -remoteFile $file) {
        $deleteCount++
    }
    Start-Sleep -Milliseconds 500
}

Write-Host ""
Write-Host "✅ Deleted $deleteCount invoice files" -ForegroundColor Green

# Step 3: Instructions for migration
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "STEP 3: Run Database Migration" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "⚠️  IMPORTANT: You must now run the migration script!" -ForegroundColor Yellow
Write-Host ""
Write-Host "Open this URL in your browser:" -ForegroundColor White
Write-Host "https://1stop.gr/migrate_remove_invoices.php?run=remove_invoices_2025" -ForegroundColor Green
Write-Host ""
Write-Host "This will:" -ForegroundColor White
Write-Host "  1. Add 'invoiced_at' column to projects table" -ForegroundColor Gray
Write-Host "  2. Migrate data from invoices to projects" -ForegroundColor Gray
Write-Host "  3. Drop invoice_items table" -ForegroundColor Gray
Write-Host "  4. Drop invoices table" -ForegroundColor Gray
Write-Host ""

# Step 4: Open migration URL
$openMigration = Read-Host "Do you want to open the migration URL now? (Y/N)"
if ($openMigration -eq "Y" -or $openMigration -eq "y") {
    Start-Process "https://1stop.gr/migrate_remove_invoices.php?run=remove_invoices_2025"
    Write-Host ""
    Write-Host "✅ Migration page opened in browser" -ForegroundColor Green
}

# Summary
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "DEPLOYMENT SUMMARY" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "✅ Files uploaded: $uploadCount" -ForegroundColor Green
Write-Host "✅ Files deleted: $deleteCount" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Next steps:" -ForegroundColor Yellow
Write-Host "   1. Run the migration script (link above)" -ForegroundColor Gray
Write-Host "   2. Test the site: https://1stop.gr" -ForegroundColor Gray
Write-Host "   3. Verify 'Τιμολόγια' menu is removed" -ForegroundColor Gray
Write-Host "   4. Test changing project status to 'Τιμολογημένο'" -ForegroundColor Gray
Write-Host "   5. Check Reports page" -ForegroundColor Gray
Write-Host ""
Write-Host "🎉 Deployment script completed!" -ForegroundColor Cyan
Write-Host ""

# Cleanup suggestion
Write-Host "⚠️  After migration succeeds, remember to:" -ForegroundColor Yellow
Write-Host "   - Delete migrate_remove_invoices.php from production" -ForegroundColor Gray
Write-Host "   - Delete test scripts (test_invoice_creation.php, etc.)" -ForegroundColor Gray
Write-Host ""
