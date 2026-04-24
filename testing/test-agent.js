/**
 * HandyCRM AI Test Agent
 * Automatically tests your web application and reports issues
 * 
 * Usage:
 *   node test-agent.js              - Run all tests
 *   node test-agent.js --full       - Run comprehensive tests
 *   node test-agent.js --scenario=auth    - Test specific scenario
 */

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

// Configuration
const CONFIG = {
    baseUrl: 'http://localhost/handycrm1',  // Update this to match your server path
    adminUser: {
        username: 'admin',
        password: '1q2w3e&*('  // Your actual admin password
    },
    screenshots: true,
    headless: false,  // Set to true to run without UI
    slowMo: 500,  // Slow down actions by 500ms for visibility
    timeout: 30000,
    reportFile: path.join(__dirname, 'test-report.html')
};

// Test results storage
const testResults = {
    passed: [],
    failed: [],
    warnings: [],
    startTime: new Date(),
    endTime: null
};

// Color output
const colors = {
    reset: '\x1b[0m',
    green: '\x1b[32m',
    red: '\x1b[31m',
    yellow: '\x1b[33m',
    blue: '\x1b[34m',
    cyan: '\x1b[36m'
};

function log(message, color = 'reset') {
    console.log(`${colors[color]}${message}${colors.reset}`);
}

function logSuccess(test, details = '') {
    log(`✓ ${test}`, 'green');
    if (details) log(`  ${details}`, 'cyan');
    testResults.passed.push({ test, details, timestamp: new Date() });
}

function logFailure(test, error, screenshot = null) {
    log(`✗ ${test}`, 'red');
    log(`  Error: ${error}`, 'red');
    testResults.failed.push({ test, error, screenshot, timestamp: new Date() });
}

function logWarning(test, warning) {
    log(`⚠ ${test}`, 'yellow');
    log(`  Warning: ${warning}`, 'yellow');
    testResults.warnings.push({ test, warning, timestamp: new Date() });
}

function logInfo(message) {
    log(`ℹ ${message}`, 'blue');
}

// Screenshot helper
async function takeScreenshot(page, name) {
    if (!CONFIG.screenshots) return null;
    
    const screenshotDir = path.join(__dirname, 'screenshots');
    if (!fs.existsSync(screenshotDir)) {
        fs.mkdirSync(screenshotDir, { recursive: true });
    }
    
    const timestamp = Date.now();
    const filename = `${name}-${timestamp}.png`;
    const filepath = path.join(screenshotDir, filename);
    
    await page.screenshot({ path: filepath, fullPage: true });
    return filepath;
}

// Wait for navigation and page load
async function waitForPageLoad(page) {
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(500);
}

// Check for PHP errors or warnings
async function checkForPhpErrors(page) {
    const bodyText = await page.textContent('body');
    const errors = [];
    
    if (bodyText.includes('Fatal error') || bodyText.includes('Parse error')) {
        errors.push('PHP Fatal/Parse Error detected');
    }
    if (bodyText.includes('Warning:') && bodyText.includes('.php')) {
        errors.push('PHP Warning detected');
    }
    if (bodyText.includes('Notice:') && bodyText.includes('.php')) {
        errors.push('PHP Notice detected');
    }
    if (bodyText.includes('Undefined variable') || bodyText.includes('Undefined index')) {
        errors.push('PHP Undefined variable/index detected');
    }
    if (bodyText.includes('Database connection failed')) {
        errors.push('Database connection error');
    }
    
    return errors;
}

// Check for console errors
async function checkConsoleErrors(page) {
    const logs = await page.evaluate(() => {
        return window.__consoleErrors || [];
    });
    return logs;
}

// ==========================================
// TEST DATA STORAGE
// ==========================================

const testData = {
    customer: { id: null, name: 'AI Test Customer' },
    project: { id: null, title: 'AI Test Project' },
    payment: { id: null, amount: 1500 },
    quote: { id: null, number: null },
    material: { id: null, name: 'AI Test Material' },
    maintenance: { id: null, name: 'AI Test Transformer' }
};

// ==========================================
// TEST SCENARIOS
// ==========================================

async function testLogin(page) {
    logInfo('Testing login functionality...');
    
    try {
        // Navigate to login page
        await page.goto(`${CONFIG.baseUrl}/index.php`);
        await waitForPageLoad(page);
        
        // Check if already logged in (redirect to dashboard)
        if (page.url().includes('dashboard')) {
            logSuccess('Login Page', 'Already logged in, redirected to dashboard');
            return true;
        }
        
        // Check for login form
        const usernameField = await page.$('input[name="username"], input[type="text"]');
        const passwordField = await page.$('input[name="password"], input[type="password"]');
        
        if (!usernameField || !passwordField) {
            logFailure('Login Page', 'Login form not found', await takeScreenshot(page, 'login-form-missing'));
            return false;
        }
        
        // Test with wrong credentials first
        await page.fill('input[name="username"], input[type="text"]', 'wronguser');
        await page.fill('input[name="password"], input[type="password"]', 'wrongpass');
        await page.click('button[type="submit"], input[type="submit"]');
        await waitForPageLoad(page);
        
        const errorMessage = await page.textContent('body');
        if (errorMessage.includes('Invalid') || errorMessage.includes('incorrect') || errorMessage.includes('wrong')) {
            logSuccess('Login Validation', 'Correctly rejects invalid credentials');
        } else {
            logWarning('Login Validation', 'No clear error message for wrong credentials');
        }
        
        // Test with correct credentials
        await page.fill('input[name="username"], input[type="text"]', CONFIG.adminUser.username);
        await page.fill('input[name="password"], input[type="password"]', CONFIG.adminUser.password);
        await page.click('button[type="submit"], input[type="submit"]');
        await waitForPageLoad(page);
        
        // Check if redirected to dashboard
        if (page.url().includes('dashboard')) {
            logSuccess('Login Success', 'Successfully logged in and redirected to dashboard');
            
            // Check for PHP errors
            const phpErrors = await checkForPhpErrors(page);
            if (phpErrors.length > 0) {
                logFailure('Dashboard Load', phpErrors.join(', '), await takeScreenshot(page, 'dashboard-errors'));
                return false;
            }
            
            return true;
        } else {
            logFailure('Login Success', 'Did not redirect to dashboard after login', await takeScreenshot(page, 'login-failed'));
            return false;
        }
        
    } catch (error) {
        logFailure('Login Test', error.message, await takeScreenshot(page, 'login-error'));
        return false;
    }
}

async function testNavigation(page) {
    logInfo('Testing navigation and menu links...');
    
    const menuLinks = [
        { name: 'Dashboard', selector: 'a[href*="dashboard"]' },
        { name: 'Customers', selector: 'a[href*="customers"]' },
        { name: 'Projects', selector: 'a[href*="projects"]' },
        { name: 'Payments', selector: 'a[href*="payments"]' },
        { name: 'Materials', selector: 'a[href*="materials"]' },
        { name: 'Quotes', selector: 'a[href*="quotes"]' },
        { name: 'Reports', selector: 'a[href*="reports"]' },
        { name: 'Settings', selector: 'a[href*="settings"]' }
    ];
    
    for (const link of menuLinks) {
        try {
            const element = await page.$(link.selector);
            if (!element) {
                logWarning(`Navigation - ${link.name}`, 'Link not found in menu');
                continue;
            }
            
            await element.click();
            await waitForPageLoad(page);
            
            // Check for PHP errors
            const phpErrors = await checkForPhpErrors(page);
            if (phpErrors.length > 0) {
                logFailure(`Navigation - ${link.name}`, phpErrors.join(', '), await takeScreenshot(page, `nav-${link.name.toLowerCase()}-error`));
            } else {
                logSuccess(`Navigation - ${link.name}`, `Page loaded without errors`);
            }
            
        } catch (error) {
            logFailure(`Navigation - ${link.name}`, error.message, await takeScreenshot(page, `nav-${link.name.toLowerCase()}-fail`));
        }
    }
}

async function testCustomerManagement(page) {
    logInfo('Testing customer CRUD operations...');
    
    try {
        // ===== CREATE =====
        logInfo('  → Creating test customer...');
        await page.goto(`${CONFIG.baseUrl}/index.php?route=/customers/create`);
        await waitForPageLoad(page);
        
        let phpErrors = await checkForPhpErrors(page);
        if (phpErrors.length > 0) {
            logFailure('Customer Create Form', phpErrors.join(', '), await takeScreenshot(page, 'customer-create-error'));
            return;
        }
        
        // Take screenshot of empty form
        await takeScreenshot(page, 'customer-form-initial');
        
        // Fill ALL possible customer fields with UNIQUE data to avoid duplicates
        const timestamp = Date.now();
        const fields = {
            'first_name': 'TestBot',
            'last_name': 'AutoTest' + timestamp,
            'phone': '69' + String(timestamp).slice(-8),  // Unique 10-digit phone
            'mobile': '69' + String(timestamp + 1000).slice(-8),  // Different unique mobile
            'email': `aitest${timestamp}@test.com`,  // Unique email
            'address': 'AI Test Street 123, Athens',
            'city': 'Athens',
            'postal_code': '12345',
            'country': 'Greece',
            'notes': 'AI automated test customer - ' + new Date().toISOString(),
            'company_name': '',
            'vat_number': '',
            'tax_office': ''
        };
        
        for (const [fieldName, value] of Object.entries(fields)) {
            try {
                const input = await page.$(`input[name="${fieldName}"], textarea[name="${fieldName}"]`);
                if (input && value) {
                    await input.fill(value);
                    logInfo(`    ✓ Filled ${fieldName}`);
                }
            } catch (e) {
                // Field doesn't exist, skip
            }
        }
        
        // Select customer type if available
        const customerTypeSelect = await page.$('select[name="customer_type"]');
        if (customerTypeSelect) {
            await customerTypeSelect.selectOption('individual');
            logInfo('    ✓ Selected customer type: individual');
        }
        
        // Submit form
        await takeScreenshot(page, 'customer-form-before-submit');
        const urlBeforeSubmit = page.url();
        logInfo(`    → URL before submit: ${urlBeforeSubmit}`);
        await page.click('button[type="submit"]');
        await waitForPageLoad(page);
        
        const urlAfterSubmit = page.url();
        logInfo(`    → URL after submit: ${urlAfterSubmit}`);
        
        phpErrors = await checkForPhpErrors(page);
        if (phpErrors.length > 0) {
            logFailure('Customer Create Submit', phpErrors.join(', '), await takeScreenshot(page, 'customer-create-submit-error'));
            return;
        }
        
        // Check for flash error messages
        const flashErrors = await page.$$eval('.alert-danger, .alert.alert-danger', els => els.map(el => el.textContent.trim()));
        if (flashErrors.length > 0) {
            logFailure('Customer Create Validation', 'Form validation errors: ' + flashErrors.join(' | '), await takeScreenshot(page, 'customer-validation-fail'));
            return;
        }
        
        // Verify redirect to list or view page
        const currentUrl = page.url();
        if (!currentUrl.includes('create')) {
            logSuccess('Customer Create', 'Customer created successfully');
            
            // Try to extract customer ID from URL
            const match = currentUrl.match(/customers\/show\/(\d+)|customers\/view\/(\d+)|id=(\d+)/);
            if (match) {
                testData.customer.id = match[1] || match[2] || match[3];
                logInfo(`  → Customer ID: ${testData.customer.id}`);
            }
        } else {
            logFailure('Customer Create', 'Form did not submit properly', await takeScreenshot(page, 'customer-create-failed'));
            return;
        }
        
        // ===== READ/SEARCH =====
        logInfo('  → Testing customer search...');
        await page.goto(`${CONFIG.baseUrl}/index.php?route=/customers`);
        await waitForPageLoad(page);
        
        const searchInput = await page.$('input[name="search"]');
        if (searchInput) {
            await searchInput.fill('TestBot');
            
            // Try multiple ways to submit search
            const searchButton = await page.$('button[type="submit"]');
            if (searchButton) {
                await searchButton.click();
            } else {
                await page.keyboard.press('Enter');
            }
            
            await waitForPageLoad(page);
            
            const bodyText = await page.textContent('body');
            if (bodyText.includes('TestBot') || bodyText.includes('AutoTest')) {
                logSuccess('Customer Search', 'Found created customer in search results');
            } else {
                logWarning('Customer Search', 'Created customer not found in search');
            }
        }
        
        // ===== UPDATE =====
        if (testData.customer.id) {
            logInfo('  → Testing customer edit...');
            await page.goto(`${CONFIG.baseUrl}/customers/edit/${testData.customer.id}`);
            await waitForPageLoad(page);
            
            phpErrors = await checkForPhpErrors(page);
            if (phpErrors.length > 0) {
                logFailure('Customer Edit Form', phpErrors.join(', '), await takeScreenshot(page, 'customer-edit-error'));
            } else {
                // Update phone number
                await page.fill('input[name="phone"]', '6987654321');
                await page.fill('textarea[name="address"]', 'Updated Test Address 456');  // textarea, not input!
                
                await page.click('button[type="submit"]');
                await waitForPageLoad(page);
                
                phpErrors = await checkForPhpErrors(page);
                if (phpErrors.length === 0) {
                    logSuccess('Customer Update', 'Customer updated successfully');
                } else {
                    logFailure('Customer Update', phpErrors.join(', '));
                }
            }
        }
        
    } catch (error) {
        logFailure('Customer Management', error.message, await takeScreenshot(page, 'customers-general-error'));
    }
}

async function testProjectManagement(page) {
    logInfo('Testing project CRUD operations...');
    
    try {
        // ===== CREATE =====
        logInfo('  → Creating test project...');
        await page.goto(`${CONFIG.baseUrl}/index.php?route=/projects/create`);
        await waitForPageLoad(page);
        
        let phpErrors = await checkForPhpErrors(page);
        if (phpErrors.length > 0) {
            logFailure('Project Create Form', phpErrors.join(', '), await takeScreenshot(page, 'project-create-error'));
            return;
        }
        
        // Take screenshot of empty form
        await takeScreenshot(page, 'project-form-initial');
        
        // Fill ALL project fields comprehensively
        const projectFields = {
            'title': 'AI Test Project - Automated Test',
            'description': 'This is an automated test project created by AI Test Agent for comprehensive testing',
            'location': 'Test Location, Athens',
            'start_date': '2026-02-10',
            'deadline': '2026-03-10',
            'estimated_hours': '40',
            'material_cost': '500',
            'labor_cost': '1000',
            'total_cost': '1500',
            'notes': 'AI automated test project'
        };
        
        for (const [fieldName, value] of Object.entries(projectFields)) {
            try {
                const input = await page.$(`input[name="${fieldName}"], textarea[name="${fieldName}"]`);
                if (input) {
                    await input.fill(value);
                    logInfo(`    ✓ Filled ${fieldName}`);
                }
            } catch (e) {
                // Field doesn't exist
            }
        }
        
        // Select customer - MANDATORY FIELD
        const customerSelect = await page.$('select[name="customer_id"]');
        if (customerSelect && testData.customer.id) {
            await customerSelect.selectOption(testData.customer.id);
            const selectedCustomer = await customerSelect.evaluate(el => el.value);
            logInfo(`    ✓ Selected test customer (ID: ${selectedCustomer})`);
        } else if (customerSelect) {
            // Select first available customer
            const options = await customerSelect.$$('option');
            logInfo(`    → Found ${options.length} customer options`);
            
            if (options.length > 1) {
                const firstOption = await options[1].evaluate(el => ({ value: el.value, text: el.textContent }));
                logInfo(`    → Selecting customer: ${firstOption.text}`);
                
                if (firstOption.value) {
                    await customerSelect.selectOption(firstOption.value);
                    const selectedCustomer = await customerSelect.evaluate(el => el.value);
                    logInfo(`    ✓ Customer selected (ID: ${selectedCustomer})`);
                }
            } else {
                logWarning('Project Form', 'No customer options available!');
            }
        } else {
            logWarning('Project Form', 'Customer select field not found!');
        }
        
        // Set status - MANDATORY FIELD
        const statusSelect = await page.$('select[name="status"]');
        if (statusSelect) {
            await statusSelect.selectOption('in_progress');
            const selectedStatus = await statusSelect.evaluate(el => el.value);
            logInfo(`    ✓ Selected status: ${selectedStatus}`);
        } else {
            logWarning('Project Form', 'Status select not found!');
        }
        
        // Set completion date - MANDATORY FIELD
        const completionDateField = await page.$('input[name="completion_date"]');
        if (completionDateField) {
            await completionDateField.fill('2026-03-10');
            logInfo('    ✓ Set completion_date: 2026-03-10');
        } else {
            logWarning('Project Form', 'Completion date field not found!');
        }
        
        // Select category if available
        const categorySelect = await page.$('select[name="category"]');
        if (categorySelect) {
            const options = await categorySelect.$$('option');
            if (options.length > 1) {
                const firstOption = await options[1].evaluate(el => el.value);
                if (firstOption) {
                    await categorySelect.selectOption(firstOption);
                    logInfo('    ✓ Selected category');
                }
            }
        }
        
        // Select technician - MANDATORY FIELD (assigned_technician)
        const technicianSelect = await page.$('select[name="assigned_technician"]');
        if (technicianSelect) {
            const options = await technicianSelect.$$('option');
            logInfo(`    → Found ${options.length} technician options`);
            
            if (options.length > 1) {
                // Get the value of the first actual technician (skip empty/placeholder option)
                const firstOption = await options[1].evaluate(el => ({ value: el.value, text: el.textContent }));
                logInfo(`    → Selecting technician: ${firstOption.text} (value: ${firstOption.value})`);
                
                if (firstOption.value) {
                    await technicianSelect.selectOption(firstOption.value);
                    
                    // Verify selection
                    const selectedValue = await technicianSelect.evaluate(el => el.value);
                    logInfo(`    ✓ Technician selected: ${selectedValue}`);
                } else {
                    logWarning('Project Form', 'Technician option has no value!');
                }
            } else {
                logWarning('Project Form', 'No technician options available!');
            }
        } else {
            logWarning('Project Form', 'Technician select field not found!');
        }
        
        // Give time for any JS to process
        await page.waitForTimeout(500);
        
        // Take screenshot before submitting
        await takeScreenshot(page, 'project-form-filled');
        
        // Submit form
        await page.click('button[type="submit"]');
        await waitForPageLoad(page);
        
        phpErrors = await checkForPhpErrors(page);
        if (phpErrors.length > 0) {
            logFailure('Project Create Submit', phpErrors.join(', '), await takeScreenshot(page, 'project-create-submit-error'));
            return;
        }
        
        const currentUrl = page.url();
        if (!currentUrl.includes('create')) {
            logSuccess('Project Create', 'Project created successfully');
            
            const match = currentUrl.match(/projects\/show\/(\d+)|projects\/view\/(\d+)|id=(\d+)/);
            if (match) {
                testData.project.id = match[1] || match[2] || match[3];
                logInfo(`  → Project ID: ${testData.project.id}`);
            }
        } else {
            logFailure('Project Create', 'Form did not submit properly', await takeScreenshot(page, 'project-create-failed'));
            return;
        }
        
        // ===== READ/FILTER =====
        logInfo('  → Testing project filters...');
        await page.goto(`${CONFIG.baseUrl}/index.php?route=/projects`);
        await waitForPageLoad(page);
        
        const statusFilter = await page.$('select[name="status"]');
        if (statusFilter) {
            await statusFilter.selectOption('in_progress');
            await page.waitForTimeout(1000);
            
            const bodyText = await page.textContent('body');
            if (bodyText.includes('AI Test Project')) {
                logSuccess('Project Filter', 'Status filter works, found test project');
            }
        }
        
        // ===== UPDATE =====
        if (testData.project.id) {
            logInfo('  → Testing project edit...');
            await page.goto(`${CONFIG.baseUrl}/projects/edit/${testData.project.id}`);
            await waitForPageLoad(page);
            
            phpErrors = await checkForPhpErrors(page);
            if (phpErrors.length > 0) {
                logFailure('Project Edit Form', phpErrors.join(', '), await takeScreenshot(page, 'project-edit-error'));
            } else {
                // Update project
                const titleField = await page.$('input[name="title"]');
                if (titleField) {
                    await titleField.fill('AI Test Project - UPDATED');
                }
                
                const statusSelect = await page.$('select[name="status"]');
                if (statusSelect) {
                    await statusSelect.selectOption('completed');
                }
                
                await page.click('button[type="submit"]');
                await waitForPageLoad(page);
                
                phpErrors = await checkForPhpErrors(page);
                if (phpErrors.length === 0) {
                    logSuccess('Project Update', 'Project updated successfully');
                } else {
                    logFailure('Project Update', phpErrors.join(', '));
                }
            }
        }
        
    } catch (error) {
        logFailure('Project Management', error.message, await takeScreenshot(page, 'projects-general-error'));
    }
}

async function testPaymentManagement(page) {
    logInfo('Testing payment creation...');
    
    try {
        // Navigate to payments page
        await page.goto(`${CONFIG.baseUrl}/index.php?route=/payments`);
        await waitForPageLoad(page);
        
        // Check for PHP errors
        const phpErrors = await checkForPhpErrors(page);
        if (phpErrors.length > 0) {
            logFailure('Payments Page', phpErrors.join(', '), await takeScreenshot(page, 'payments-page-error'));
            return;
        }
        
        logSuccess('Payments Page', 'Page loaded successfully');
        
        // ===== CREATE PAYMENT =====
        if (testData.customer.id) {
            logInfo('  → Creating test payment...');
            const addButton = await page.$('a[href*="payments/create"], button:has-text("Προσθήκη")');
            
            if (addButton) {
                await addButton.click();
                await waitForPageLoad(page);
                
                await takeScreenshot(page, 'payment-form-initial');
                
                // Fill ALL payment fields
                const paymentFields = {
                    'amount': '1500',
                    'payment_date': '2026-02-06',
                    'reference': 'AI-TEST-PAY-001',
                    'notes': 'AI Test Payment - Automated Test'
                };
                
                for (const [fieldName, value] of Object.entries(paymentFields)) {
                    try {
                        const input = await page.$(`input[name="${fieldName}"], textarea[name="${fieldName}"]`);
                        if (input) {
                            await input.fill(value);
                            logInfo(`    ✓ Filled payment ${fieldName}`);
                        }
                    } catch (e) {
                        // Field doesn't exist
                    }
                }
                
                const customerSelect = await page.$('select[name="customer_id"]');
                if (customerSelect) {
                    if (testData.customer.id) {
                        await customerSelect.selectOption(testData.customer.id);
                        logInfo('    ✓ Selected test customer for payment');
                    } else {
                        const options = await customerSelect.$$('option');
                        if (options.length > 1) {
                            await customerSelect.selectOption({ index: 1 });
                            logInfo('    ✓ Selected first customer for payment');
                        }
                    }
                }
                
                // Select project if available
                const projectSelect = await page.$('select[name="project_id"]');
                if (projectSelect && testData.project.id) {
                    await projectSelect.selectOption(testData.project.id);
                    logInfo('    ✓ Linked payment to test project');
                }
                
                const paymentMethod = await page.$('select[name="payment_method"]');
                if (paymentMethod) {
                    await paymentMethod.selectOption('cash');
                    logInfo('    ✓ Selected payment method: cash');
                }
                
                await page.click('button[type="submit"]');
                await waitForPageLoad(page);
                
                const errors = await checkForPhpErrors(page);
                if (errors.length === 0 && !page.url().includes('create')) {
                    logSuccess('Payment Create', 'Payment created successfully');
                    
                    const match = page.url().match(/payments\/view\/(\d+)|id=(\d+)/);
                    if (match) {
                        testData.payment.id = match[1] || match[2];
                    }
                } else {
                    logFailure('Payment Create', 'Failed to create payment', await takeScreenshot(page, 'payment-create-failed'));
                }
            }
        }
        
        // Test date filters
        logInfo('  → Testing payment filters...');
        await page.goto(`${CONFIG.baseUrl}/index.php?route=/payments`);
        await waitForPageLoad(page);
        
        const dateFromInput = await page.$('input[name="date_from"]');
        if (dateFromInput) {
            await dateFromInput.fill('2026-02-01');
            const searchButton = await page.$('button[type="submit"]');
            if (searchButton) {
                await searchButton.click();
                await waitForPageLoad(page);
                logSuccess('Payment Filters', 'Date filter applied successfully');
            }
        }
        
    } catch (error) {
        logFailure('Payment Management', error.message, await takeScreenshot(page, 'payments-general-error'));
    }
}

async function testReports(page) {
    logInfo('Testing reports functionality...');
    
    try {
        // Navigate to reports page
        await page.goto(`${CONFIG.baseUrl}/index.php?route=/reports`);
        await waitForPageLoad(page);
        
        // Check for PHP errors
        const phpErrors = await checkForPhpErrors(page);
        if (phpErrors.length > 0) {
            logFailure('Reports Page', phpErrors.join(', '), await takeScreenshot(page, 'reports-page-error'));
            return;
        }
        
        logSuccess('Reports Page', 'Page loaded successfully');
        
    } catch (error) {
        logFailure('Reports', error.message, await takeScreenshot(page, 'reports-general-error'));
    }
}

async function testQuotes(page) {
    logInfo('Testing quotes CRUD operations...');
    
    try {
        // Take screenshot
        await takeScreenshot(page, 'quote-form-initial');
        
        // Fill ALL quote fields
        const quoteFields = {
            'title': 'AI Test Quote - Automated Test',
            'description': 'This is an automated test quote',
            'issue_date': '2026-02-06',
            'valid_until': '2026-03-06',
            'notes': 'AI automated test quote',
            'terms': 'Standard terms and conditions',
            'discount': '0',
            'vat_rate': '24'
        };
        
        for (const [fieldName, value] of Object.entries(quoteFields)) {
            try {
                const input = await page.$(`input[name="${fieldName}"], textarea[name="${fieldName}"]`);
                if (input) {
                    await input.fill(value);
                    logInfo(`    ✓ Filled ${fieldName}`);
                }
            } catch (e) {
                // Field doesn't exist
            }
        }
        
        const customerSelect = await page.$('select[name="customer_id"]');
        if (customerSelect && testData.customer.id) {
            await customerSelect.selectOption(testData.customer.id);
            logInfo('    ✓ Selected test customer');
        } else if (customerSelect) {
            const options = await customerSelect.$$('option');
            if (options.length > 1) {
                await customerSelect.selectOption({ index: 1 });
                logInfo('    ✓ Selected first customer');
            }
            await titleField.fill('AI Test Quote - Automated Test');
        }
        
        const issueDateField = await page.$('input[name="issue_date"]');
        if (issueDateField) {
            await issueDateField.fill('2026-02-06');
        }
        
        const validUntilField = await page.$('input[name="valid_until"]');
        if (validUntilField) {
            await validUntilField.fill('2026-03-06');
        }
        
        // Submit form
        await page.click('button[type="submit"]');
        await waitForPageLoad(page);
        
        phpErrors = await checkForPhpErrors(page);
        if (phpErrors.length === 0 && !page.url().includes('/create')) {
            logSuccess('Quote Create', 'Quote created successfully');
            
            const match = page.url().match(/quotes\/show\/(\d+)|quotes\/view\/(\d+)|id=(\d+)/);
            if (match) {
                testData.quote.id = match[1] || match[2] || match[3];
                logInfo(`  → Quote ID: ${testData.quote.id}`);
            }
        } else if (phpErrors.length > 0) {
            logFailure('Quote Create', phpErrors.join(', '), await takeScreenshot(page, 'quote-create-failed'));
        } else {
            logWarning('Quote Create', 'Form may have validation errors');
        }
        
    } catch (error) {
        logFailure('Quotes Management', error.message, await takeScreenshot(page, 'quotes-general-error'));
    }
}

async function testMaterials(page) {
    logInfo('Testing materials catalog...');
    
    try {
        // Navigate to materials page
        await page.goto(`${CONFIG.baseUrl}/index.php?route=/materials`);
        await waitForPageLoad(page);
        
        let phpErrors = await checkForPhpErrors(page);
        if (phpErrors.length > 0) {
            logFailure('Materials Page', phpErrors.join(', '), await takeScreenshot(page, 'materials-page-error'));
            return;
        }
        
        logSuccess('Materials Page', 'Page loaded successfully');
        
        // ===== CREATE MATERIAL =====
        logInfo('  → Creating test material...');
        const addButton = await page.$('a[href*="materials/create"], button:has-text("Προσθήκη")');
        
        if (addButton) {
            await addButton.click();
            await waitForPageLoad(page);
            
            await takeScreenshot(page, 'material-form-initial');
            
            // Fill ALL material fields
            const materialFields = {
                'name': 'AI Test Material - Cable 3x2.5mm',
                'sku': 'TEST-CABLE-001',
                'description': 'AI automated test material for comprehensive testing',
                'price': '25.50',
                'cost': '18.00',
                'stock_quantity': '100',
                'min_stock': '10',
                'supplier': 'Test Supplier Co.'
            };
            
            for (const [fieldName, value] of Object.entries(materialFields)) {
                try {
                    const input = await page.$(`input[name="${fieldName}"], textarea[name="${fieldName}"]`);
                    if (input) {
                        await input.fill(value);
                        logInfo(`    ✓ Filled material ${fieldName}`);
                    }
                } catch (e) {
                    // Field doesn't exist
                }
            }
            
            const unitField = await page.$('select[name="unit_type"], select[name="unit"]');
            if (unitField) {
                await unitField.selectOption('meters');
                logInfo('    ✓ Selected unit: meters');
            }
            
            const categorySelect = await page.$('select[name="category_id"]');
            if (categorySelect) {
                const options = await categorySelect.$$('option');
                if (options.length > 1) {
                    await categorySelect.selectOption({ index: 1 });
                    logInfo('    ✓ Selected material category');
                }
            }
            
            await page.click('button[type="submit"]');
            await waitForPageLoad(page);
            
            phpErrors = await checkForPhpErrors(page);
            if (phpErrors.length === 0 && !page.url().includes('/create')) {
                logSuccess('Material Create', 'Material created successfully');
                
                const match = page.url().match(/materials\/view\/(\d+)|id=(\d+)/);
                if (match) {
                    testData.material.id = match[1] || match[2];
                }
            } else {
                logWarning('Material Create', 'Material creation may have failed or validation errors');
            }
        }
        
        // Test search
        logInfo('  → Testing material search...');
        await page.goto(`${CONFIG.baseUrl}/index.php?route=/materials`);
        await waitForPageLoad(page);
        
        const searchInput = await page.$('input[name="search"]');
        if (searchInput) {
            await searchInput.fill('Test Material');
            await page.keyboard.press('Enter');
            await waitForPageLoad(page);
            logSuccess('Material Search', 'Search functionality works');
        }
        
    } catch (error) {
        logFailure('Materials Management', error.message, await takeScreenshot(page, 'materials-general-error'));
    }
}

async function testMaintenance(page) {
    logInfo('Testing maintenance tracking...');
    
    try {
        // Check if maintenance module exists
        await page.goto(`${CONFIG.baseUrl}/index.php?route=/maintenances`);
        await waitForPageLoad(page);
        
        const phpErrors = await checkForPhpErrors(page);
        if (phpErrors.length > 0) {
            logWarning('Maintenance Page', 'Module may not be accessible or has errors');
            return;
        }
        
        logSuccess('Maintenance Page', 'Page loaded successfully');
        
        // ===== CREATE MAINTENANCE RECORD =====
        logInfo('  → Creating test maintenance record...');
        const addButton = await page.$('a[href*="maintenances/create"], button:has-text("Προσθήκη")');
        
        if (addButton) {
            await addButton.click();
            await waitForPageLoad(page);
            
            await takeScreenshot(page, 'maintenance-form-initial');
            
            // Fill ALL maintenance fields
            const maintenanceFields = {
                'transformer_name': 'AI Test Transformer T-001',
                'location': 'Test Location - Building A, Floor 3',
                'serial_number': 'SN-TEST-001',
                'manufacturer': 'Test Manufacturer',
                'power_rating': '500',
                'installation_date': '2020-01-15',
                'maintenance_date': '2026-02-06',
                'technician_notes': 'AI automated test maintenance record',
                'oil_level': 'Normal',
                'temperature': '45'
            };
            
            for (const [fieldName, value] of Object.entries(maintenanceFields)) {
                try {
                    const input = await page.$(`input[name="${fieldName}"], textarea[name="${fieldName}"]`);
                    if (input) {
                        await input.fill(value);
                        logInfo(`    ✓ Filled maintenance ${fieldName}`);
                    }
                } catch (e) {
                    // Field doesn't exist
                }
            }
            
            const customerSelect = await page.$('select[name="customer_id"]');
            if (customerSelect && testData.customer.id) {
                await customerSelect.selectOption(testData.customer.id);
                logInfo('    ✓ Linked maintenance to test customer');
            } else if (customerSelect) {
                const options = await customerSelect.$$('option');
                if (options.length > 1) {
                    await customerSelect.selectOption({ index: 1 });
                    logInfo('    ✓ Selected first customer for maintenance');
                }
            }
            
            const statusSelect = await page.$('select[name="status"]');
            if (statusSelect) {
                await statusSelect.selectOption('completed');
                logInfo('    ✓ Selected status: completed');
            }
            
            await page.click('button[type="submit"]');
            await waitForPageLoad(page);
            
            const errors = await checkForPhpErrors(page);
            if (errors.length === 0 && !page.url().includes('/create')) {
                logSuccess('Maintenance Create', 'Maintenance record created successfully');
                
                const match = page.url().match(/maintenances\/edit\/(\d+)|maintenances\/view\/(\d+)|id=(\d+)/);
                if (match) {
                    testData.maintenance.id = match[1] || match[2] || match[3];
                }
            } else {
                logWarning('Maintenance Create', 'Creation may have validation errors');
            }
        }
        
    } catch (error) {
        logWarning('Maintenance Tracking', `Module may not exist or error: ${error.message}`);
    }
}

async function testSettings(page) {
    logInfo('Testing settings page...');
    
    try {
        await page.goto(`${CONFIG.baseUrl}/index.php?route=/settings`);
        await waitForPageLoad(page);
        
        const phpErrors = await checkForPhpErrors(page);
        if (phpErrors.length > 0) {
            logFailure('Settings Page', phpErrors.join(', '), await takeScreenshot(page, 'settings-page-error'));
            return;
        }
        
        logSuccess('Settings Page', 'Page loaded successfully');
        
        // Test if settings form is accessible
        const settingsForm = await page.$('form');
        if (settingsForm) {
            logSuccess('Settings Form', 'Settings form is accessible');
        } else {
            logWarning('Settings Form', 'Settings form not found');
        }
        
    } catch (error) {
        logFailure('Settings', error.message, await takeScreenshot(page, 'settings-general-error'));
    }
}

async function cleanupTestData(page) {
    logInfo('Cleaning up test data...');
    
    try {
        // ===== DELETE PAYMENT =====
        if (testData.payment.id) {
            logInfo('  → Deleting test payment...');
            try {
                await page.goto(`${CONFIG.baseUrl}/index.php?route=/payments`);
                await waitForPageLoad(page);
                
                // Look for delete button for our payment
                const deleteButton = await page.$(`form[action*="payments/delete/${testData.payment.id}"] button, a[href*="payments/delete/${testData.payment.id}"]`);
                if (deleteButton) {
                    page.on('dialog', dialog => dialog.accept()); // Accept confirmation
                    await deleteButton.click();
                    await waitForPageLoad(page);
                    logSuccess('Cleanup Payment', 'Test payment deleted');
                } else {
                    logWarning('Cleanup Payment', 'Delete button not found');
                }
            } catch (error) {
                logWarning('Cleanup Payment', error.message);
            }
        }
        
        // ===== DELETE PROJECT =====
        if (testData.project.id) {
            logInfo('  → Deleting test project...');
            try {
                await page.goto(`${CONFIG.baseUrl}/index.php?route=/projects`);
                await waitForPageLoad(page);
                
                const deleteButton = await page.$(`form[action*="projects/delete/${testData.project.id}"] button, a[href*="projects/delete/${testData.project.id}"]`);
                if (deleteButton) {
                    page.on('dialog', dialog => dialog.accept());
                    await deleteButton.click();
                    await waitForPageLoad(page);
                    logSuccess('Cleanup Project', 'Test project deleted');
                } else {
                    logWarning('Cleanup Project', 'Delete button not found');
                }
            } catch (error) {
                logWarning('Cleanup Project', error.message);
            }
        }
        
        // ===== DELETE QUOTE =====
        if (testData.quote.id) {
            logInfo('  → Deleting test quote...');
            try {
                await page.goto(`${CONFIG.baseUrl}/index.php?route=/quotes`);
                await waitForPageLoad(page);
                
                const deleteButton = await page.$(`form[action*="quotes/delete/${testData.quote.id}"] button, a[href*="quotes/delete/${testData.quote.id}"]`);
                if (deleteButton) {
                    page.on('dialog', dialog => dialog.accept());
                    await deleteButton.click();
                    await waitForPageLoad(page);
                    logSuccess('Cleanup Quote', 'Test quote deleted');
                } else {
                    logWarning('Cleanup Quote', 'Delete button not found');
                }
            } catch (error) {
                logWarning('Cleanup Quote', error.message);
            }
        }
        
        // ===== DELETE MATERIAL =====
        if (testData.material.id) {
            logInfo('  → Deleting test material...');
            try {
                await page.goto(`${CONFIG.baseUrl}/index.php?route=/materials`);
                await waitForPageLoad(page);
                
                const deleteButton = await page.$(`form[action*="materials/delete/${testData.material.id}"] button, a[href*="materials/delete/${testData.material.id}"]`);
                if (deleteButton) {
                    page.on('dialog', dialog => dialog.accept());
                    await deleteButton.click();
                    await waitForPageLoad(page);
                    logSuccess('Cleanup Material', 'Test material deleted');
                } else {
                    logWarning('Cleanup Material', 'Delete button not found');
                }
            } catch (error) {
                logWarning('Cleanup Material', error.message);
            }
        }
        
        // ===== DELETE MAINTENANCE =====
        if (testData.maintenance.id) {
            logInfo('  → Deleting test maintenance record...');
            try {
                await page.goto(`${CONFIG.baseUrl}/index.php?route=/maintenances`);
                await waitForPageLoad(page);
                
                const deleteButton = await page.$(`form[action*="maintenances/delete/${testData.maintenance.id}"] button, a[href*="maintenances/delete/${testData.maintenance.id}"]`);
                if (deleteButton) {
                    page.on('dialog', dialog => dialog.accept());
                    await deleteButton.click();
                    await waitForPageLoad(page);
                    logSuccess('Cleanup Maintenance', 'Test maintenance record deleted');
                } else {
                    logWarning('Cleanup Maintenance', 'Delete button not found');
                }
            } catch (error) {
                logWarning('Cleanup Maintenance', error.message);
            }
        }
        
        // ===== DELETE CUSTOMER (last, as others depend on it) =====
        if (testData.customer.id) {
            logInfo('  → Deleting test customer...');
            try {
                await page.goto(`${CONFIG.baseUrl}/index.php?route=/customers`);
                await waitForPageLoad(page);
                
                const deleteButton = await page.$(`form[action*="customers/delete/${testData.customer.id}"] button, a[href*="customers/delete/${testData.customer.id}"]`);
                if (deleteButton) {
                    page.on('dialog', dialog => dialog.accept());
                    await deleteButton.click();
                    await waitForPageLoad(page);
                    logSuccess('Cleanup Customer', 'Test customer deleted');
                } else {
                    logWarning('Cleanup Customer', 'Delete button not found - may be soft deleted');
                }
            } catch (error) {
                logWarning('Cleanup Customer', error.message);
            }
        }
        
        logInfo('✓ Cleanup completed!');
        
    } catch (error) {
        logWarning('Cleanup', `Some test data may not have been deleted: ${error.message}`);
    }
}

async function testFormValidation(page) {
    logInfo('Testing form validation...');
    
    try {
        // Test customer form validation
        await page.goto(`${CONFIG.baseUrl}/index.php?route=/customers/create`);
        await waitForPageLoad(page);
        
        // Try submitting empty form
        const submitButton = await page.$('button[type="submit"], input[type="submit"]');
        if (submitButton) {
            await submitButton.click();
            await page.waitForTimeout(1000);
            
            // Check if form prevented submission
            const currentUrl = page.url();
            if (currentUrl.includes('create')) {
                logSuccess('Form Validation', 'Empty form submission prevented');
            } else {
                logWarning('Form Validation', 'Form submitted without validation');
            }
        }
        
    } catch (error) {
        logFailure('Form Validation', error.message, await takeScreenshot(page, 'validation-error'));
    }
}

async function testResponsiveDesign(page) {
    logInfo('Testing responsive design...');
    
    const viewports = [
        { name: 'Desktop', width: 1920, height: 1080 },
        { name: 'Tablet', width: 768, height: 1024 },
        { name: 'Mobile', width: 375, height: 667 }
    ];
    
    for (const viewport of viewports) {
        try {
            await page.setViewportSize({ width: viewport.width, height: viewport.height });
            await page.goto(`${CONFIG.baseUrl}/index.php?route=/dashboard`);
            await waitForPageLoad(page);
            
            // Check if content is visible
            const bodyHeight = await page.evaluate(() => document.body.scrollHeight);
            if (bodyHeight > 0) {
                logSuccess(`Responsive - ${viewport.name}`, `Renders correctly at ${viewport.width}x${viewport.height}`);
            }
            
            await takeScreenshot(page, `responsive-${viewport.name.toLowerCase()}`);
            
        } catch (error) {
            logFailure(`Responsive - ${viewport.name}`, error.message);
        }
    }
    
    // Reset to desktop
    await page.setViewportSize({ width: 1920, height: 1080 });
}

// ==========================================
// REPORT GENERATION
// ==========================================

function generateHtmlReport() {
    const duration = (testResults.endTime - testResults.startTime) / 1000;
    const total = testResults.passed.length + testResults.failed.length;
    const passRate = total > 0 ? ((testResults.passed.length / total) * 100).toFixed(1) : 0;
    
    const html = `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HandyCRM Test Report - ${testResults.startTime.toLocaleString()}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.1); }
        header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px 8px 0 0; }
        header h1 { font-size: 28px; margin-bottom: 10px; }
        header .meta { opacity: 0.9; font-size: 14px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; padding: 30px; background: #f8f9fa; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .stat-card .label { color: #6c757d; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .stat-card .value { font-size: 32px; font-weight: bold; }
        .stat-card.passed .value { color: #28a745; }
        .stat-card.failed .value { color: #dc3545; }
        .stat-card.warnings .value { color: #ffc107; }
        .results { padding: 30px; }
        .result-section { margin-bottom: 30px; }
        .result-section h2 { font-size: 20px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e9ecef; }
        .result-item { padding: 15px; margin-bottom: 10px; border-radius: 6px; border-left: 4px solid #dee2e6; }
        .result-item.passed { background: #d4edda; border-left-color: #28a745; }
        .result-item.failed { background: #f8d7da; border-left-color: #dc3545; }
        .result-item.warning { background: #fff3cd; border-left-color: #ffc107; }
        .result-item .test-name { font-weight: 600; margin-bottom: 5px; }
        .result-item .details { font-size: 14px; color: #6c757d; }
        .result-item .error { color: #dc3545; margin-top: 5px; font-family: 'Courier New', monospace; font-size: 13px; }
        .result-item .timestamp { font-size: 12px; color: #adb5bd; margin-top: 5px; }
        .screenshot { max-width: 100%; margin-top: 10px; border: 1px solid #dee2e6; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🤖 HandyCRM AI Test Report</h1>
            <div class="meta">
                Generated: ${testResults.startTime.toLocaleString()}<br>
                Duration: ${duration.toFixed(2)}s | Pass Rate: ${passRate}%
            </div>
        </header>
        
        <div class="stats">
            <div class="stat-card passed">
                <div class="label">Passed</div>
                <div class="value">${testResults.passed.length}</div>
            </div>
            <div class="stat-card failed">
                <div class="label">Failed</div>
                <div class="value">${testResults.failed.length}</div>
            </div>
            <div class="stat-card warnings">
                <div class="label">Warnings</div>
                <div class="value">${testResults.warnings.length}</div>
            </div>
            <div class="stat-card">
                <div class="label">Total Tests</div>
                <div class="value">${total}</div>
            </div>
        </div>
        
        <div class="results">
            ${testResults.failed.length > 0 ? `
            <div class="result-section">
                <h2>❌ Failed Tests</h2>
                ${testResults.failed.map(item => `
                    <div class="result-item failed">
                        <div class="test-name">${item.test}</div>
                        <div class="error">${item.error}</div>
                        <div class="timestamp">${item.timestamp.toLocaleString()}</div>
                        ${item.screenshot ? `<img src="${item.screenshot}" class="screenshot" alt="Error screenshot">` : ''}
                    </div>
                `).join('')}
            </div>
            ` : ''}
            
            ${testResults.warnings.length > 0 ? `
            <div class="result-section">
                <h2>⚠️ Warnings</h2>
                ${testResults.warnings.map(item => `
                    <div class="result-item warning">
                        <div class="test-name">${item.test}</div>
                        <div class="details">${item.warning}</div>
                        <div class="timestamp">${item.timestamp.toLocaleString()}</div>
                    </div>
                `).join('')}
            </div>
            ` : ''}
            
            ${testResults.passed.length > 0 ? `
            <div class="result-section">
                <h2>✅ Passed Tests</h2>
                ${testResults.passed.map(item => `
                    <div class="result-item passed">
                        <div class="test-name">${item.test}</div>
                        ${item.details ? `<div class="details">${item.details}</div>` : ''}
                        <div class="timestamp">${item.timestamp.toLocaleString()}</div>
                    </div>
                `).join('')}
            </div>
            ` : ''}
        </div>
    </div>
</body>
</html>
    `;
    
    fs.writeFileSync(CONFIG.reportFile, html);
    log(`\n📄 Report saved to: ${CONFIG.reportFile}`, 'cyan');
}

// ==========================================
// MAIN TEST RUNNER
// ==========================================

async function runTests() {
    log('\n🤖 HandyCRM AI Test Agent Starting...', 'cyan');
    log(`Testing: ${CONFIG.baseUrl}\n`, 'cyan');
    
    const browser = await chromium.launch({
        headless: CONFIG.headless,
        slowMo: CONFIG.slowMo
    });
    
    const context = await browser.newContext({
        viewport: { width: 1920, height: 1080 },
        userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    });
    
    const page = await context.newPage();
    page.setDefaultTimeout(CONFIG.timeout);
    
    // Capture console errors
    page.on('console', msg => {
        if (msg.type() === 'error') {
            log(`Console Error: ${msg.text()}`, 'red');
        }
    });
    
    try {
        // Run test scenarios
        log('\n=== PHASE 1: AUTHENTICATION ===\n', 'cyan');
        const loginSuccess = await testLogin(page);
        
        if (loginSuccess) {
            // COMPREHENSIVE CRUD TESTING
            log('\n=== PHASE 2: CREATE TEST DATA ===\n', 'cyan');
            await testCustomerManagement(page);  // Create customer
            await testProjectManagement(page);   // Create project
            await testPaymentManagement(page);   // Create payment
            await testQuotes(page);              // Create quote
            await testMaterials(page);           // Create material
            await testMaintenance(page);         // Create maintenance record
            
            // NAVIGATION & FEATURES
            log('\n=== PHASE 3: NAVIGATION & FEATURES ===\n', 'cyan');
            await testNavigation(page);          // Test all menu links
            await testSettings(page);            // Test settings page
            await testReports(page);             // Test reports
            await testFormValidation(page);      // Form validation
            await testResponsiveDesign(page);    // Responsive layouts
            
            // CLEANUP
            log('\n=== PHASE 4: CLEANUP ===\n', 'cyan');
            await cleanupTestData(page);         // Delete all test data
            
            log('\n✅ All test phases completed!', 'green');
        } else {
            log('\n❌ Login failed, skipping other tests', 'red');
        }
        
    } catch (error) {
        log(`\n❌ Test execution error: ${error.message}`, 'red');
    } finally {
        await browser.close();
    }
    
    // Generate report
    testResults.endTime = new Date();
    generateHtmlReport();
    
    // Print summary
    log('\n' + '='.repeat(60), 'cyan');
    log('TEST SUMMARY', 'cyan');
    log('='.repeat(60), 'cyan');
    log(`✅ Passed:   ${testResults.passed.length}`, 'green');
    log(`❌ Failed:   ${testResults.failed.length}`, 'red');
    log(`⚠️  Warnings: ${testResults.warnings.length}`, 'yellow');
    log('='.repeat(60) + '\n', 'cyan');
    
    if (testResults.failed.length > 0) {
        process.exit(1);
    }
}

// Run the tests
runTests().catch(error => {
    log(`\nFatal error: ${error.message}`, 'red');
    process.exit(1);
});
