# 🚀 How to Create GitHub Release v1.2.5

## Steps to Create Release on GitHub

1. **Push commits and tags to GitHub:**
```bash
git push origin main
git push origin v1.2.5
```

2. **Go to GitHub Repository:**
- Navigate to: https://github.com/TheoSfak/handycrm
- Click on "Releases" (right sidebar)

3. **Create New Release:**
- Click "Draft a new release"
- Click "Choose a tag" → Select `v1.2.5`
- Release title: **HandyCRM v1.2.5 - Production-Ready Materials Catalog**

4. **Release Description:**
Copy and paste from `RELEASE_NOTES_v1.2.5.md` or use the text below:

---

## 📊 What's New in v1.2.5

### CSV Export/Import/Demo System
Manage your materials catalog efficiently with bulk operations:

- **📤 Export CSV** - Download all materials in Excel-ready format (UTF-8 BOM)
- **📥 Import CSV** - Bulk upload from spreadsheet with validation
- **📋 Demo Template** - Pre-built CSV with 5 sample materials

### 100 Real Electrical Materials Pre-Loaded
Ready-to-use catalog with comprehensive electrical materials:
- Cables (NYM, NYA, LIYY)
- Conduits (CONDUR, FLEX)
- Switches & Outlets
- Circuit Breakers
- Junction Boxes
- LED Lighting
- Grounding Equipment
- Accessories (WAGO, sensors, relays, timers)

### Smart Pagination
- Default: 25 items per page
- Adjustable: 10, 25, 50, or 100 items
- Maintains filters across pages

### Standardized Units
18 predefined units dropdown:
`τεμ, μ, μ², μ³, κιλά, λίτρα, κιβώτιο, σετ, κουτί, ρολό, παλέτα, τόνος, πακέτο, δέσμη, φύλλο, τρέχον μέτρο, ώρα, ημέρα`

### Enhanced Search
- Auto-generated Greeklish + English aliases for all materials
- Universal search: "kalodio", "cable", or "nym" finds "Καλώδιο NYM 3x1.5"

### UI Improvements
- 4-column labor layout
- CSV button group in materials header
- Full English translations

---

## 📦 Installation

### New Installation
```bash
1. Extract files to web server
2. Navigate to http://yoursite.com/handycrm/install.php
3. Follow installation wizard
4. 100 materials loaded automatically
```

### Upgrade from v1.2.0
```bash
1. Backup your database
2. Copy new files over existing installation
3. Run migrations:
   - migrations/load_electrical_materials.sql
   - migrations/load_electrical_materials_part2.sql
   - php migrations/regenerate_material_aliases.php
4. Done!
```

---

## 🔧 Technical Details

**Files Modified:**
- `index.php` - CSV routes
- `controllers/MaterialsController.php` - exportCSV(), importCSV()
- `views/materials/index.php` - CSV UI + JavaScript
- `models/MaterialCatalog.php` - Pagination support
- `languages/en.json`, `languages/el.json` - Translations

**Database:**
- 100 new materials inserted
- Auto-generated aliases for search
- No schema changes

**Dependencies:** None (uses existing Bootstrap, jQuery)

---

## 🐛 Bug Fixes
- Fixed pagination display issues
- Fixed inconsistent unit names
- Fixed search for new materials
- Fixed labor tab spacing

---

## 📚 Full Documentation
- [README.md](README.md)
- [CHANGELOG.md](CHANGELOG.md)
- [RELEASE_NOTES_v1.2.5.md](RELEASE_NOTES_v1.2.5.md)

---

**Ready for production deployment! 🎉**

---

5. **Assets (Optional):**
- No binaries to attach for this release
- All code is in the repository

6. **Pre-release:**
- Leave unchecked (this is a stable release)

7. **Create Discussion:**
- Check "Create a discussion for this release" if you want community feedback

8. **Publish Release:**
- Click "Publish release"

---

## 🎯 Post-Release Checklist

- [ ] Release published on GitHub
- [ ] Tag `v1.2.5` pushed to repository
- [ ] README.md updated with new version
- [ ] CHANGELOG.md updated with release notes
- [ ] Test installation from release on clean environment
- [ ] Update production server (1stop.gr) if applicable
- [ ] Announce release to users/clients
- [ ] Archive release notes for future reference

---

## 📧 Release Announcement Template

**Subject:** HandyCRM v1.2.5 Released - CSV Import/Export & 100 Materials!

**Body:**

Hi HandyCRM Users! 👋

We're excited to announce **HandyCRM v1.2.5** - our most production-ready release yet!

**Key Highlights:**
✅ CSV Export/Import for bulk material management
✅ 100 pre-loaded electrical materials (cables, switches, LEDs, etc.)
✅ Smart pagination (25/page, adjustable)
✅ Standardized unit dropdown
✅ Auto-generated search aliases (Greeklish + English)

**Perfect for:**
- Electrical contractors
- Plumbing businesses
- Technical service companies

**Download:** https://github.com/TheoSfak/handycrm/releases/tag/v1.2.5

**Upgrade Guide:** See CHANGELOG.md for migration steps

Questions? Email: theodore.sfakianakis@gmail.com

Happy CRM-ing! 🎉

Theodore Sfakianakis

---

## ✅ Verification

After publishing, verify:
- Release appears on: https://github.com/TheoSfak/handycrm/releases
- Tag `v1.2.5` is visible
- Release notes are formatted correctly
- Links work properly
- Download/clone works

---

**All files are ready! You can now proceed with the GitHub release. 🚀**
