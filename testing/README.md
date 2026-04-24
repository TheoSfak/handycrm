# 🤖 HandyCRM AI Test Agent

An intelligent automated testing system that explores your HandyCRM web application, tests all features, and reports bugs automatically.

## ✨ Features

- **Automated Login Testing** - Tests authentication with valid/invalid credentials
- **Complete Navigation Testing** - Visits every page and checks for errors
- **CRUD Operations Testing** - Tests Create, Read, Update, Delete for all modules
- **Form Validation Testing** - Ensures forms validate properly
- **Responsive Design Testing** - Tests on Desktop, Tablet, and Mobile viewports
- **PHP Error Detection** - Automatically detects Fatal errors, Warnings, Notices
- **Beautiful HTML Reports** - Generates comprehensive test reports with screenshots
- **Real-time Console Output** - See test progress with color-coded results

## 🚀 Quick Start

### 1. Install Dependencies

```bash
cd testing
npm install
```

### 2. Install Browsers (First Time Only)

```bash
npm run install-browsers
```

### 3. Configure Your Settings

Edit `test-agent.js` and update:

```javascript
const CONFIG = {
    baseUrl: 'http://localhost/handycrm1',  // Your HandyCRM URL
    adminUser: {
        username: 'admin',     // Your admin username
        password: 'admin123'   // Your admin password
    },
    headless: false,  // Set to true to run without browser UI
};
```

### 4. Run Tests

```bash
# Run all tests
npm test

# Run specific scenario
npm run test:auth
npm run test:customers
npm run test:projects
```

## 📊 Test Reports

After running tests, open:
- **HTML Report**: `testing/test-report.html`
- **Screenshots**: `testing/screenshots/`

The report includes:
- ✅ Passed tests (green)
- ❌ Failed tests with error details (red)
- ⚠️ Warnings (yellow)
- 📸 Screenshots of errors
- ⏱️ Test duration and pass rate

## 🧪 What Gets Tested

### Authentication
- Login form validation
- Invalid credential rejection
- Successful login & redirect
- Session persistence

### Navigation
- All menu links work
- Pages load without PHP errors
- No broken links

### Customer Management
- Customer list page loads
- Add customer form accessible
- Search functionality works
- Form validation active

### Project Management
- Project list page loads
- Create project form accessible
- Status filters work
- Date filters functional

### Payment Management
- Payment list displays
- Date range filtering
- Export functionality
- Payment records display correctly

### Reports
- Reports page accessible
- No fatal errors
- Data displays correctly

### Responsive Design
- Desktop (1920x1080)
- Tablet (768x1024)
- Mobile (375x667)

## 🔧 Configuration Options

Edit `test-agent.js` CONFIG section:

```javascript
const CONFIG = {
    baseUrl: 'http://localhost/handycrm1',
    adminUser: { username: 'admin', password: 'admin123' },
    screenshots: true,          // Enable/disable screenshots
    headless: false,            // Run browser in background
    slowMo: 100,               // Slow down actions (ms)
    timeout: 30000,            // Page load timeout (ms)
};
```

## 📝 Adding Custom Tests

Add your own test scenarios in `test-agent.js`:

```javascript
async function testMyFeature(page) {
    logInfo('Testing my custom feature...');
    
    try {
        await page.goto(`${CONFIG.baseUrl}/index.php?route=/my-feature`);
        await waitForPageLoad(page);
        
        const phpErrors = await checkForPhpErrors(page);
        if (phpErrors.length > 0) {
            logFailure('My Feature', phpErrors.join(', '));
        } else {
            logSuccess('My Feature', 'Works perfectly!');
        }
    } catch (error) {
        logFailure('My Feature', error.message);
    }
}

// Then add to runTests():
await testMyFeature(page);
```

## 🎯 CI/CD Integration

Run headless tests in your pipeline:

```bash
# package.json
"scripts": {
    "test:ci": "node test-agent.js --headless"
}
```

```yaml
# GitHub Actions example
- name: Run HandyCRM Tests
  run: |
    cd testing
    npm install
    npm run test:ci
```

## 🐛 Troubleshooting

**Browser not found:**
```bash
npm run install-browsers
```

**Connection refused:**
- Make sure your HandyCRM is running
- Update `baseUrl` in CONFIG
- Check XAMPP/WAMP is started

**Login fails:**
- Verify admin credentials in CONFIG
- Check database has admin user
- Ensure session is working

## 📦 What's Installed

- **Playwright** - Browser automation framework
- **Chromium** - Headless browser for testing
- **Chalk** - Colorful console output

## 💡 Tips

1. **Run with browser visible** (`headless: false`) first time to see what's happening
2. **Check screenshots folder** when tests fail to see what went wrong
3. **Use slowMo** to slow down execution and watch the automation
4. **Add more test scenarios** specific to your business logic
5. **Schedule regular runs** to catch regressions early

## 🎨 Output Colors

- 🟢 **Green**: Test passed
- 🔴 **Red**: Test failed
- 🟡 **Yellow**: Warning (non-critical issue)
- 🔵 **Blue**: Information

## 🚀 Next Steps

1. Run your first test: `npm test`
2. Review the HTML report
3. Fix any issues found
4. Add custom tests for your specific features
5. Integrate into your development workflow

---

**Made with ❤️ for HandyCRM**
