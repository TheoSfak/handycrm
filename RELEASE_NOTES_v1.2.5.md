# 🚀 HandyCRM v1.2.5 - Production-Ready Materials Catalog

**Release Date:** October 17, 2025  
**Focus:** Bulk Operations, Data Quality, Production Deployment

---

## 📊 What's New

### CSV Export/Import/Demo System
Manage your materials catalog efficiently with bulk operations:

- **📤 Export CSV** - Download all materials in Excel-ready format
  - UTF-8 BOM encoding for perfect Greek character display
  - All data included: name, description, category, unit, price, stock, supplier info
  - Timestamped filenames: `materials_export_2025-10-17_143052.csv`
  
- **📥 Import CSV** - Bulk upload from spreadsheet
  - Drag-and-drop or file picker
  - Smart validation with line-by-line error reporting
  - Category auto-matching
  - Confirmation dialog before import
  
- **📋 Demo Template** - Pre-built CSV with 5 sample materials
  - Shows exact format required
  - Real electrical materials examples
  - Edit in Excel and re-import immediately

### 100 Real Electrical Materials Pre-Loaded
Ready-to-use catalog with comprehensive electrical materials:

**Categories Include:**
- Cables: NYM, NYA, LIYY in various sizes
- Conduits: CONDUR, FLEX pipes and fittings
- Switches & Outlets: Wall-mounted, surface, IP-rated
- Circuit Breakers: MCBs, RCDs, various amperages
- Junction Boxes: IP55, IP65, different sizes
- LED Lighting: Bulbs, strips, power supplies
- Grounding: Rods, clamps, cables
- Accessories: WAGO connectors, cable ties, ducts, relays, sensors, timers

All materials have:
- ✅ Greek names and descriptions
- ✅ Proper categories
- ✅ Realistic prices
- ✅ Standard units
- ✅ Auto-generated search aliases (Greeklish + English)

### Smart Pagination
Navigate large catalogs efficiently:
- Default: 25 items per page (optimal performance)
- Adjustable: 10, 25, 50, or 100 items per page
- Clean Bootstrap pagination UI
- Maintains search filters across pages

### Standardized Units
Replaced free-text input with dropdown containing 18 predefined units:
- τεμ, μ, μ², μ³, κιλά, λίτρα, κιβώτιο, σετ, κουτί, ρολό, παλέτα, τόνος, πακέτο, δέσμη, φύλλο, τρέχον μέτρο, ώρα, ημέρα

Prevents inconsistencies like "τεμ" vs "τεμάχια" vs "τεμ." in your database.

### Enhanced Search
- **Auto-Generated Aliases**: All 100 materials have Greeklish + English search terms
- **Universal Search**: Type "kalodio", "cable", or "nym" to find "Καλώδιο NYM 3x1.5"
- **Batch Regeneration**: Script included to regenerate aliases for existing materials

### UI Improvements
- **4-Column Labor Layout**: Better space utilization in project labor tab
- **CSV Button Group**: Professional action buttons in materials header
- **English Translations**: Full internationalization for units and dates

---

## 🎯 Why This Release?

**v1.2.5** makes HandyCRM truly production-ready for electrical and plumbing businesses:

1. **Time Saver**: Import 100+ materials in seconds instead of manual entry
2. **Data Quality**: Standardized units prevent reporting inconsistencies  
3. **Easy Backup**: Export your entire catalog anytime
4. **Quick Start**: 100 materials included - start using immediately
5. **Excel-Friendly**: CSV format works seamlessly with Excel/LibreOffice
6. **Search Power**: Auto-aliases ensure you find materials fast

---

## 📦 Installation

### New Installation
```bash
1. Extract files to your web server
2. Navigate to http://yoursite.com/handycrm/install.php
3. Follow installation wizard
4. 100 materials will be loaded automatically
```

### Upgrade from v1.2.0
```bash
1. Backup your database
2. Copy new files over existing installation
3. Run migrations:
   - migrations/load_electrical_materials.sql
   - migrations/load_electrical_materials_part2.sql
   - php migrations/regenerate_material_aliases.php
4. Done! CSV features ready to use
```

---

## 🔧 Technical Details

### Files Modified
- `index.php` - Added CSV export/import routes
- `controllers/MaterialsController.php` - New exportCSV() and importCSV() methods
- `views/materials/index.php` - CSV UI and JavaScript
- `views/materials/form.php` - Unit dropdown
- `views/projects/show.php` - 4-column labor layout
- `languages/en.json`, `languages/el.json` - New translations
- `models/MaterialCatalog.php` - Pagination support

### New Migration Files
- `migrations/load_electrical_materials.sql` - First 50 materials
- `migrations/load_electrical_materials_part2.sql` - Remaining 50 materials
- `migrations/regenerate_material_aliases.php` - Batch alias generation

### Database Impact
- No schema changes (uses existing `materials_catalog` table from v1.2.0)
- 100 new material rows inserted
- All materials have populated `aliases` column for search

### Dependencies
- No new external dependencies
- Uses existing: Bootstrap 5, jQuery, Lightbox2, Chart.js

---

## 🐛 Bug Fixes

- Fixed pagination not showing on initial materials load
- Fixed unit field accepting inconsistent free-text values
- Fixed search not working for newly added materials
- Fixed labor tab spacing on large screens
- Fixed CSV export including inactive materials

---

## 📸 Screenshots

### CSV Operations
![CSV Buttons](https://via.placeholder.com/800x200/4CAF50/ffffff?text=Export+%7C+Import+%7C+Demo+CSV+Buttons)

### Pagination System
![Pagination](https://via.placeholder.com/800x100/2196F3/ffffff?text=25+items+per+page+with+10%2F25%2F50%2F100+selector)

### Unit Dropdown
![Unit Dropdown](https://via.placeholder.com/400x300/FF9800/ffffff?text=18+Standardized+Units)

### 4-Column Labor Layout
![Labor Layout](https://via.placeholder.com/800x400/9C27B0/ffffff?text=4-Column+Grid+Layout)

---

## 🙏 Credits

**Developed by:** Theodore Sfakianakis  
**Email:** theodore.sfakianakis@gmail.com  
**License:** Proprietary - © 2025 Theodore Sfakianakis. All rights reserved.

---

## 📚 Documentation

- Full documentation: [README.md](README.md)
- Complete changelog: [CHANGELOG.md](CHANGELOG.md)
- Installation guide: [INSTALL.md](INSTALL.md)

---

## 🔜 What's Next?

**v1.3.0 Roadmap:**
- Invoice generation and printing
- Advanced reporting and analytics
- Client portal for project tracking
- Mobile app (iOS/Android)
- Email notifications for project updates

---

## 💬 Support

For questions, bug reports, or feature requests:
- Email: theodore.sfakianakis@gmail.com
- GitHub Issues: [Create an issue](https://github.com/TheoSfak/handycrm/issues)

---

**Happy CRM-ing! 🎉**
