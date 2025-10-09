# 🚀 Quick Start: GitHub + Auto-Update System

## ✅ What You Get:

1. **GitHub Repository** - Version control & releases
2. **Auto-Update Checker** - Notifies users of new versions
3. **One-Click Notifications** - Alert banner in dashboard
4. **Release Management** - Organized changelog & downloads

---

## 📋 Setup in 10 Minutes:

### Step 1: Create GitHub Account (if you don't have one)
Go to https://github.com/signup

### Step 2: Create Repository
```
1. Go to https://github.com/new
2. Name: handycrm
3. Description: Professional CRM System
4. Public/Private: Your choice
5. Don't initialize with files
6. Click "Create repository"
```

### Step 3: Push Code to GitHub

Open PowerShell in `C:\Users\user\Desktop\handycrm\`:

```powershell
# Initialize git
git init

# Add all files
git add .

# First commit
git commit -m "Initial commit - HandyCRM v1.0.0"

# Add GitHub remote (REPLACE 'YOUR_USERNAME')
git remote add origin https://github.com/YOUR_USERNAME/handycrm.git

# Push to GitHub
git branch -M main
git push -u origin main
```

### Step 4: Create First Release

```
1. Go to your repo: https://github.com/YOUR_USERNAME/handycrm
2. Click "Releases" (right sidebar)
3. Click "Create a new release"
4. Tag: v1.0.0
5. Title: HandyCRM v1.0.0 - Initial Release
6. Description: (copy from GITHUB_SETUP.md)
7. Click "Publish release"
```

### Step 5: Update Configuration

Edit `classes/UpdateChecker.php`:

```php
public $githubRepo = 'YOUR_USERNAME/handycrm'; // Replace with your username!
```

Upload this file to ALL your installations (desktop + online).

---

## 🎯 That's It! Now It Works:

### What Happens Now:

1. ✅ Your code is on GitHub
2. ✅ Version 1.0.0 is released
3. ✅ Any HandyCRM installation will check GitHub daily
4. ✅ When you release v1.0.1, users see notification
5. ✅ They click to see release notes and download

---

## 🔄 When You Want to Release an Update:

### Quick Update Process:

```powershell
# 1. Make your changes to the code

# 2. Update version number
# Edit VERSION file: 1.0.1
# Edit classes/UpdateChecker.php: private $currentVersion = '1.0.1';

# 3. Commit and push
git add .
git commit -m "Update to v1.0.1 - Bug fixes"
git push

# 4. Create release on GitHub
#    Go to: https://github.com/YOUR_USERNAME/handycrm/releases/new
#    Tag: v1.0.1
#    Title: HandyCRM v1.0.1 - Bug Fixes
#    Description: List what changed
#    Publish!

# 5. Done! All installations will be notified within 24 hours
```

---

## 🔔 How Users See Updates:

### In Dashboard:
```
┌─────────────────────────────────────────────────┐
│ 📥 Νέα Έκδοση Διαθέσιμη: v1.0.1                 │
│ Η τρέχουσα έκδοση είναι v1.0.0                  │
│ ─────────────────────────────────────────────── │
│ [Προβολή Ενημέρωσης] [Release Notes] [x]       │
└─────────────────────────────────────────────────┘
```

### In Settings → Updates:
- See current version
- See available updates
- Download links
- Step-by-step upgrade instructions
- Changelog

---

## 📊 Version Examples:

- `1.0.0` → `1.0.1` = Bug fix (Patch)
- `1.0.1` → `1.1.0` = New feature (Minor)
- `1.1.0` → `2.0.0` = Breaking changes (Major)

---

## 🛠️ Files Created:

```
✅ .gitignore                    - Excludes sensitive files
✅ VERSION                       - Current version number
✅ README_GITHUB.md              - GitHub readme
✅ GITHUB_SETUP.md               - Detailed instructions
✅ classes/UpdateChecker.php     - Auto-update system
✅ views/settings/update.php     - Update management page
✅ QUICK_START_GITHUB.md         - This file!
```

---

## 🎓 Important Notes:

### What's Excluded from GitHub:
- `config/config.php` (passwords!)
- `uploads/` (user files)
- Log files

### What's Included:
- All source code
- `config/config.example.php` (template)
- `database/handycrm.sql` (schema)
- Documentation

### Security:
- NEVER commit `config/config.php`
- NEVER commit database dumps with real data
- GitHub repo can be private if you want

---

## 🆘 Troubleshooting:

### "Can't push to GitHub"
```powershell
# Set up git identity
git config --global user.name "Your Name"
git config --global user.email "your@email.com"

# Use personal access token for authentication
# Go to: GitHub → Settings → Developer settings → Personal access tokens
```

### "Update notification not showing"
1. Check `githubRepo` is set correctly
2. Make sure you created a release (not just pushed code)
3. Wait 24 hours or click "Check for Updates" button
4. Check GitHub repo is public or token is configured

### "Git not found"
Download and install: https://git-scm.com/download/win

---

## 🎉 Success Checklist:

- [ ] GitHub repository created
- [ ] Code pushed to GitHub
- [ ] First release (v1.0.0) published
- [ ] UpdateChecker configured with your username
- [ ] Tested on local installation
- [ ] Uploaded to online installation
- [ ] Update notification appears correctly

---

## 📞 Next Steps:

1. ✅ Create GitHub repository
2. ✅ Push your code
3. ✅ Create v1.0.0 release
4. ✅ Update `githubRepo` in UpdateChecker
5. ✅ Upload updated files to server
6. ✅ Check dashboard for update notification
7. 🎯 You're done!

---

**Your HandyCRM is now professional-grade with GitHub integration!** 🚀

All future updates will be automatically notified to all installations!