# HandyCRM Multi-Language System

## Overview
HandyCRM now supports multiple languages! The system comes with **Greek (Ελληνικά)** and **English** by default, and users can add their own languages through the translation manager.

## Features
- ✅ Built-in Greek and English translations
- ✅ Easy language switcher in Settings
- ✅ Translation manager for adding/editing languages
- ✅ Real-time translation progress tracking
- ✅ Search and filter translations
- ✅ User-specific language preferences
- ✅ Session-based language storage

## How to Use Translations in Code

### Basic Translation Function

```php
// Use the __() function (double underscore)
echo __('menu.dashboard');  // Output: "Dashboard" (EN) or "Πίνακας Ελέγχου" (EL)

// With default fallback
echo __('some.missing.key', 'Default Text');  // Output: "Default Text" if key doesn't exist

// Alternative function name
echo trans('menu.customers');  // Same as __()
```

### Translation Keys Structure

Translation keys use **dot notation** for nested structures:

```php
__('app.name')              // "HandyCRM"
__('menu.dashboard')        // "Dashboard" or "Πίνακας Ελέγχου"
__('customers.add_customer') // "Add Customer" or "Προσθήκη Πελάτη"
__('common.save')           // "Save" or "Αποθήκευση"
```

### Example: Converting Existing Views

**Before:**
```php
<h2>Πελάτες</h2>
<button>Προσθήκη Πελάτη</button>
```

**After:**
```php
<h2><?= __('customers.title') ?></h2>
<button><?= __('customers.add_customer') ?></button>
```

## Available Translation Categories

1. **app** - Application metadata
2. **menu** - Navigation menu items
3. **dashboard** - Dashboard page
4. **customers** - Customer management
5. **projects** - Project management
6. **invoices** - Invoice management
7. **tasks** - Task management
8. **settings** - Settings pages
9. **updates** - Update system
10. **translations** - Translation manager
11. **common** - Common terms (save, cancel, delete, etc.)
12. **notifications** - Notification messages
13. **user** - User account pages

## For Users: How to Change Language

1. Go to **Settings** (Ρυθμίσεις)
2. Find **Language / Γλώσσα** section
3. Select your preferred language from dropdown
4. Changes apply immediately!

## For Users: How to Add a New Language

1. Go to **Settings → Translations** (Ρυθμίσεις → Μεταφράσεις)
2. Click **Create New Language** (Δημιουργία Νέας Γλώσσας)
3. Enter:
   - **Language Code**: 2 letters (e.g., `fr` for French, `de` for German)
   - **Language Name**: Full name (e.g., "Français", "Deutsch")
   - **Base Language**: English or Greek (structure template)
4. Click **Add Language**
5. Fill in translations:
   - Left column shows English terms
   - Right column is for your translations
6. Click **Save Translations**

## Translation Progress Tracking

The system tracks completion percentage for each language:
- ✅ **Completed**: Non-empty translations
- ⏰ **Pending**: Empty translations
- Progress bar shows percentage completed

## Search and Filter

- **Search Box**: Find specific terms quickly
- **Show Empty Only**: Focus on untranslated terms
- **Real-time Filtering**: Results update as you type

## Technical Details

### File Structure
```
/languages/
  ├── en.json     (English - Base language)
  ├── el.json     (Greek - Default)
  └── fr.json     (French - Custom, if added)

/classes/
  └── LanguageManager.php
```

### Language Files (JSON Format)
```json
{
    "menu": {
        "dashboard": "Dashboard",
        "customers": "Customers",
        "settings": "Settings"
    },
    "common": {
        "save": "Save",
        "cancel": "Cancel"
    }
}
```

### Database Storage
- User language preference saved in `users.language` column
- Session variable: `$_SESSION['language']`
- Default fallback: Greek (`el`)

## Supported Languages

### Built-in:
- 🇬🇷 **Greek (Ελληνικά)** - `el`
- 🇬🇧 **English** - `en`

### Can be added by users:
- 🇫🇷 French (Français) - `fr`
- 🇩🇪 German (Deutsch) - `de`
- 🇪🇸 Spanish (Español) - `es`
- 🇮🇹 Italian (Italiano) - `it`
- 🇵🇹 Portuguese (Português) - `pt`
- 🇷🇺 Russian (Русский) - `ru`
- 🇨🇳 Chinese (中文) - `zh`
- 🇯🇵 Japanese (日本語) - `ja`
- 🇸🇦 Arabic (العربية) - `ar`
- And many more...

## Protected Languages

Greek and English cannot be deleted (default languages). All other languages can be removed via the Translation Manager.

## Tips for Translators

1. **Keep it concise**: Match the original term's length when possible
2. **Context matters**: Read the English term carefully
3. **Save often**: Click "Save Translations" regularly
4. **Use search**: Find related terms using search box
5. **Focus mode**: Use "Show empty only" to see what needs translation

## API Usage (For Developers)

```php
// Initialize Language Manager
$lang = new LanguageManager('en');

// Get translation
$text = $lang->get('menu.dashboard');

// Get all translations
$all = $lang->getAll();

// Get available languages
$languages = $lang->getAvailableLanguages();

// Save translations
$translations = ['menu' => ['new_item' => 'New Item']];
$lang->saveTranslations('en', $translations);

// Create new language
$lang->createLanguage('fr', 'Français', 'en');

// Check progress
$progress = $lang->getTranslationProgress('fr');  // Returns 0-100
```

## Future Enhancements

- [ ] Export/Import translations as CSV
- [ ] Auto-translation using Google Translate API
- [ ] Translation approval workflow
- [ ] Community translation contributions
- [ ] RTL (Right-to-Left) language support
- [ ] Pluralization rules
- [ ] Date/Number formatting per language

---

**Need Help?**
Visit the Translation Manager at: `/settings/translations`
Or contact: theodore.sfakianakis@gmail.com
