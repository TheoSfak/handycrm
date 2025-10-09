# GitHub Setup & Update System Guide

## 📋 Step-by-Step GitHub Setup

### 1. Create GitHub Repository

1. Go to https://github.com/new
2. **Repository name:** `handycrm`
3. **Description:** "Professional CRM System with SEO-friendly URLs"
4. **Visibility:** Public (or Private if you prefer)
5. **DO NOT** initialize with README (we have one)
6. Click **Create repository**

### 2. Initialize Local Git Repository

Open terminal in your HandyCRM desktop folder:

```bash
cd C:\Users\user\Desktop\handycrm

# Initialize git
git init

# Add all files
git add .

# Create first commit
git commit -m "Initial commit - HandyCRM v1.0.0"

# Add GitHub as remote (replace YOUR_USERNAME)
git remote add origin https://github.com/YOUR_USERNAME/handycrm.git

# Push to GitHub
git branch -M main
git push -u origin main
```

### 3. Create First Release

1. Go to your GitHub repository
2. Click **"Releases"** → **"Create a new release"**
3. **Tag version:** `v1.0.0`
4. **Release title:** `HandyCRM v1.0.0 - Initial Release`
5. **Description:**
   ```markdown
   ## 🎉 Initial Release

   ### Features
   - ✅ Customer Management
   - ✅ Project Tracking
   - ✅ Invoice System
   - ✅ Quote Generation
   - ✅ SEO-Friendly URLs
   - ✅ Greek Language Support
   - ✅ One-Click Installation
   - ✅ Auto-Update Checker

   ### Installation
   1. Download ZIP from Assets below
   2. Extract to your web server
   3. Run `install.php`
   4. Login with admin@handycrm.com / admin123

   ### Requirements
   - PHP 8.0+
   - MySQL 5.7+
   - Apache with mod_rewrite
   ```
6. Click **"Publish release"**

---

## 🔄 Update Workflow

### When You Want to Release an Update:

#### Step 1: Update Version Number

Edit `VERSION` file:
```
1.0.1
```

Edit `classes/UpdateChecker.php`:
```php
private $currentVersion = '1.0.1';
```

#### Step 2: Commit Changes

```bash
cd C:\Users\user\Desktop\handycrm

# Stage your changes
git add .

# Commit with message
git commit -m "Update to v1.0.1 - Bug fixes and improvements"

# Push to GitHub
git push origin main
```

#### Step 3: Create New Release on GitHub

1. Go to repository → **Releases** → **Draft a new release**
2. **Tag:** `v1.0.1`
3. **Title:** `HandyCRM v1.0.1 - Bug Fixes`
4. **Description:** List changes:
   ```markdown
   ## 🐛 Bug Fixes
   - Fixed dashboard revenue calculation
   - Improved SEO URL generation
   - Enhanced error handling

   ## 🔧 Improvements
   - Better update checker performance
   - Optimized database queries
   ```
5. Click **Publish release**

#### Step 4: All Installations Get Notification

- Every HandyCRM installation checks GitHub API daily
- When they login, they see update notification
- They can download new version from GitHub
- Follow manual update instructions

---

## ⚙️ Configure Update Checker

Edit `classes/UpdateChecker.php` and change:

```php
public $githubRepo = 'YOUR_USERNAME/handycrm';
```

Replace `YOUR_USERNAME` with your actual GitHub username.

---

## 🔔 How Update Notification Works

### 1. Automatic Check (Daily)
```php
// Runs once per day automatically
$updateChecker->checkForUpdates();
```

### 2. GitHub API Call
```
GET https://api.github.com/repos/YOUR_USERNAME/handycrm/releases/latest
```

### 3. Version Comparison
```php
if (version_compare($githubVersion, $currentVersion, '>')) {
    // Show update notification
}
```

### 4. User Sees Notification
- Alert banner in dashboard
- "New version available" message
- Link to update page with instructions

---

## 📦 Update Process for End Users

### Option 1: Manual Update (Recommended)

1. **Backup:**
   ```bash
   mysqldump -u user -p database > backup.sql
   ```

2. **Download:** Get ZIP from GitHub releases

3. **Extract & Upload:** 
   - Keep `config/config.php` (don't replace)
   - Keep `uploads/` folder
   - Replace everything else

4. **Test:** Check if everything works

### Option 2: Semi-Automated (Future Enhancement)

Could add a controller that:
1. Downloads ZIP from GitHub
2. Extracts to temp folder
3. Copies files (excluding config & uploads)
4. Runs migration scripts
5. Shows success message

---

## 🛡️ Security Notes

### What's NOT in GitHub:

The `.gitignore` file excludes:
- `config/config.php` (contains passwords)
- `uploads/*` (user files)
- Log files
- IDE files

### What IS in GitHub:

- `config/config.example.php` (template only)
- All source code
- `database/handycrm.sql` (schema)
- Documentation

---

## 📊 Version Numbering

Follow Semantic Versioning (semver.org):

- **Major:** `2.0.0` - Breaking changes
- **Minor:** `1.1.0` - New features (backward compatible)
- **Patch:** `1.0.1` - Bug fixes

Examples:
- `1.0.0` → `1.0.1` - Bug fix
- `1.0.1` → `1.1.0` - New feature added
- `1.1.0` → `2.0.0` - Major rewrite

---

## 🚀 Quick Release Checklist

- [ ] Update `VERSION` file
- [ ] Update version in `UpdateChecker.php`
- [ ] Test all changes locally
- [ ] Commit and push to GitHub
- [ ] Create new release with tag
- [ ] Write clear release notes
- [ ] Test update notification
- [ ] Announce to users

---

## 📝 Example Release Notes Template

```markdown
## HandyCRM v1.0.1

### 🆕 New Features
- Added export to Excel functionality
- New customer import wizard

### 🐛 Bug Fixes
- Fixed invoice calculation rounding
- Resolved dashboard chart display issue

### 🔧 Improvements
- Faster database queries
- Better mobile responsiveness

### ⚠️ Breaking Changes
None

### 📋 Upgrade Instructions
1. Backup your database
2. Download and extract new version
3. Upload files (keep config.php and uploads/)
4. Clear browser cache

### 🙏 Thank You
Thanks to all users who reported issues!
```

---

## 🆘 Troubleshooting

### Update Notification Not Showing?

1. Check GitHub repo is public
2. Verify `githubRepo` in UpdateChecker.php
3. Check session is working
4. Force check by clicking "Check for Updates"

### Can't Push to GitHub?

```bash
# Set up authentication
git config --global user.name "Your Name"
git config --global user.email "your@email.com"

# Use personal access token instead of password
```

---

**Ready to go live with GitHub updates!** 🎉