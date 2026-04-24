/**
 * HandyCRM Test Data Cleanup Script
 * Deletes all test records created by test-agent.js
 */

const { chromium } = require('playwright');

const CONFIG = {
    baseUrl: 'http://localhost/handycrm1',
    username: 'admin',
    password: '1q2w3e&*('
};

async function cleanupTestData() {
    console.log('🧹 Starting test data cleanup...\n');
    
    const browser = await chromium.launch({ headless: false });
    const context = await browser.newContext();
    const page = await context.newPage();
    
    try {
        // Login first
        console.log('→ Logging in...');
        await page.goto(`${CONFIG.baseUrl}/index.php?route=/login`);
        await page.fill('input[name="username"]', CONFIG.username);
        await page.fill('input[name="password"]', CONFIG.password);
        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');
        console.log('✓ Logged in\n');
        
        // Find and delete all test customers (name contains "TestBot" or "AutoTest")
        console.log('→ Searching for test customers...');
        await page.goto(`${CONFIG.baseUrl}/index.php?route=/customers`);
        await page.waitForLoadState('networkidle');
        
        // Search for TestBot
        const searchInput = await page.$('input[name="search"]');
        if (searchInput) {
            await searchInput.fill('TestBot');
            await page.keyboard.press('Enter');
            await page.waitForLoadState('networkidle');
            
            // Look for customer rows
            const customerLinks = await page.$$('a[href*="/customers/"]');
            console.log(`  Found ${customerLinks.length} customer links`);
            
            for (const link of customerLinks) {
                const href = await link.getAttribute('href');
                const idMatch = href.match(/customers\/show\?id=(\d+)|customers\/(\d+)/);
                if (idMatch) {
                    const customerId = idMatch[1] || idMatch[2];
                    console.log(`  → Attempting to delete customer ID: ${customerId}`);
                    
                    try {
                        // Navigate to delete URL
                        await page.goto(`${CONFIG.baseUrl}/index.php?route=/customers/delete&id=${customerId}`);
                        await page.waitForTimeout(1000);
                        console.log(`  ✓ Deleted customer ID: ${customerId}`);
                    } catch (error) {
                        console.log(`  ✗ Failed to delete customer ${customerId}: ${error.message}`);
                    }
                }
            }
        }
        
        // Find and delete all test projects (title contains "AI Test")
        console.log('\n→ Searching for test projects...');
        await page.goto(`${CONFIG.baseUrl}/index.php?route=/projects`);
        await page.waitForLoadState('networkidle');
        
        const projectSearchInput = await page.$('input[name="search"]');
        if (projectSearchInput) {
            await projectSearchInput.fill('AI Test');
            await page.keyboard.press('Enter');
            await page.waitForLoadState('networkidle');
            
            const projectLinks = await page.$$('a[href*="/projects/"]');
            console.log(`  Found ${projectLinks.length} project links`);
            
            for (const link of projectLinks) {
                const href = await link.getAttribute('href');
                const idMatch = href.match(/projects\/details\?id=(\d+)|projects\/(\d+)/);
                if (idMatch) {
                    const projectId = idMatch[1] || idMatch[2];
                    console.log(`  → Attempting to delete project ID: ${projectId}`);
                    
                    try {
                        await page.goto(`${CONFIG.baseUrl}/index.php?route=/projects/delete&id=${projectId}`);
                        await page.waitForTimeout(1000);
                        console.log(`  ✓ Deleted project ID: ${projectId}`);
                    } catch (error) {
                        console.log(`  ✗ Failed to delete project ${projectId}: ${error.message}`);
                    }
                }
            }
        }
        
        // Find and delete test quotes (title contains "AI Test")
        console.log('\n→ Searching for test quotes...');
        await page.goto(`${CONFIG.baseUrl}/index.php?route=/quotes`);
        await page.waitForLoadState('networkidle');
        
        const quoteSearchInput = await page.$('input[name="search"]');
        if (quoteSearchInput) {
            await quoteSearchInput.fill('AI Test');
            await page.keyboard.press('Enter');
            await page.waitForLoadState('networkidle');
            
            const quoteLinks = await page.$$('a[href*="/quotes/"]');
            console.log(`  Found ${quoteLinks.length} quote links`);
            
            for (const link of quoteLinks) {
                const href = await link.getAttribute('href');
                const idMatch = href.match(/quotes\/details\?id=(\d+)|quotes\/(\d+)/);
                if (idMatch) {
                    const quoteId = idMatch[1] || idMatch[2];
                    console.log(`  → Attempting to delete quote ID: ${quoteId}`);
                    
                    try {
                        await page.goto(`${CONFIG.baseUrl}/quotes/delete/${quoteId}`);
                        await page.waitForTimeout(1000);
                        console.log(`  ✓ Deleted quote ID: ${quoteId}`);
                    } catch (error) {
                        console.log(`  ✗ Failed to delete quote ${quoteId}: ${error.message}`);
                    }
                }
            }
        }
        
        console.log('\n✅ Cleanup completed!');
        
    } catch (error) {
        console.error('❌ Error during cleanup:', error.message);
    } finally {
        await browser.close();
    }
}

cleanupTestData();
