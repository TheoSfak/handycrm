# HandyCRM v1.2.0 - Smart Materials Catalog & Project Photos

## 🎉 Highlights

This release brings **intelligent materials search** with Greeklish support and **project photos** functionality, making HandyCRM even more powerful for Greek technicians!

### 📸 Project Photos System
Upload and manage project photos with a beautiful gallery interface:
- **Drag & Drop Upload** - Modern file upload interface
- **Lightbox Gallery** - Click any photo for full-screen view
- **Smart Storage** - Organized by project in `uploads/projects/{id}/`
- **Auto-Cleanup** - Photos deleted when project is deleted

### 🔍 Intelligent Materials Catalog

The new materials catalog includes **breakthrough search capabilities**:

#### Greeklish Search
Type in Latin characters to find Greek materials:
- Search "**kalodio**" → finds "**Καλώδιο**"
- Search "**lampa**" → finds "**Λάμπα**"
- Works with all Greek characters including accents (ά, έ, ή, ί, ό, ύ, ώ)

#### Synonym Matching
Search in English to find Greek materials:
- Search "**cable**" → finds "**Καλώδιο**"
- Search "**lamp**" → finds "**Λάμπα**"
- Search "**pipe**" → finds "**Σωλήνας**"

#### Code Extraction
Find materials by product codes anywhere in the name:
- Search "**nym**" → finds "**Καλώδιο NYM 3x1.5**"
- Search "**3x1.5**" → finds all materials with that specification
- Case-insensitive (NYM, nym, Nym all work)

#### Auto-Generated Aliases
Every material automatically gets smart search keywords:
- **Greeklish variations**: Καλώδιο → kalodio, kalwdio
- **English synonyms**: Καλώδιο → cable, wire
- **Extracted codes**: Καλώδιο NYM 3x1.5 → NYM, nym, 3x1.5
- **Manual override**: Edit aliases directly in the material form

### ⚡ Real-Time Autocomplete
Enhanced autocomplete in task materials:
- Type 2+ characters for instant results
- Keyboard navigation (↑↓ arrows, Enter to select, Escape to close)
- Auto-fill unit and price from catalog
- Visual category badges
- Debounced API calls for performance

### 🚀 Automatic Database Migrations
No more manual SQL execution:
- Fresh installs automatically run all migrations
- Migration tracking prevents duplicates
- Error-resilient (skips existing tables/columns)
- Progress feedback during installation

## 📊 Technical Details

### New Components
- **MaterialAliasGenerator.php** - Intelligent alias generation engine
- **MaterialsController.php** - RESTful materials management
- **MaterialCatalog & MaterialCategory** models
- **material-autocomplete.js** - Enhanced autocomplete with keyboard support
- **Migration system** - Automatic database updates

### Database Changes
- New table: `materials_catalog` with FULLTEXT indexing
- New table: `material_categories`
- New table: `project_photos`
- New column: `aliases` in materials_catalog for search optimization
- FULLTEXT index on (name, aliases) for lightning-fast search

### Algorithm Improvements
- **Word-level processing** - Each word processed separately (no Greek-Latin mixing)
- **Accent handling** - All Greek diacritics properly converted
- **Priority ordering** - Exact match → Starts with → In aliases → Contains
- **Duplicate removal** - Smart filtering of redundant aliases

## 🐛 Bug Fixes
- Fixed Greeklish conversion producing mixed Greek-Latin words (e.g., "άmmos")
- Fixed accented vowels (ά, έ, ή) not converting to Latin properly
- Fixed routing conflict between old inventory and new catalog systems
- Fixed autocomplete not initializing in some cases
- Fixed project-tasks.js integration issues

## 📦 Installation

### New Installation
1. Download the [latest release](https://github.com/TheoSfak/handycrm/releases/tag/v1.2.0)
2. Extract to your web server
3. Visit `http://yoursite.com/install.php`
4. Follow the setup wizard
5. All migrations run automatically! ✨

### Upgrade from v1.1.0
1. **Backup your database first!**
2. Download and extract over existing files (keep `config/config.php`)
3. Visit `http://yoursite.com/`
4. Migrations will run automatically on first load
5. Visit `/materials/regenerate-aliases` to generate aliases for existing materials

## 🎯 What to Try First

1. **Upload Project Photos**
   - Go to any project → Photos tab
   - Drag & drop images or click to upload
   - Click any photo to view in lightbox

2. **Test Greeklish Search**
   - Go to Projects → Tasks → New Task → Add Material
   - Type "kalodio" and see "Καλώδιο" appear
   - Try "nym", "cable", "3x1.5"

3. **Add Materials to Catalog**
   - Go to Materials → Add Material
   - Enter Greek name (e.g., "Καλώδιο NYM 3x1.5")
   - See auto-generated aliases in placeholder
   - Save and search using any alias

4. **Regenerate Aliases**
   - Visit `/materials/regenerate-aliases`
   - See all generated aliases for existing materials
   - Test searching with Greeklish, synonyms, and codes

## 📚 Documentation

- **Full Changelog**: See [CHANGELOG.md](https://github.com/TheoSfak/handycrm/blob/main/CHANGELOG.md)
- **Testing Guide**: See [TESTING_GUIDE.md](https://github.com/TheoSfak/handycrm/blob/main/TESTING_GUIDE.md)
- **Migration Guide**: See [migrations/README.md](https://github.com/TheoSfak/handycrm/blob/main/migrations/README.md)

## 🙏 Credits

Developed by **Theodore Sfakianakis**  
Email: theodore.sfakianakis@gmail.com  
© 2025 All rights reserved

## 📝 License

This project is proprietary software. All rights reserved.

---

**Download**: [HandyCRM-v1.2.0.zip](https://github.com/TheoSfak/handycrm/archive/refs/tags/v1.2.0.zip)

**Enjoy the new features!** 🚀
