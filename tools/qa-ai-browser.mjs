import {chromium} from '@playwright/test';import fs from 'node:fs';
const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
const page=await browser.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
await page.goto('http://127.0.0.1:8199/language/ar?return=%2Fsmile-ai');
await page.locator('#smile-file').setInputFiles('tests/Fixtures/synthetic-smile.png');
await page.locator('input[name=consent]').check();
const unavailableButtonDisabled=await page.locator('#smile-form button[type=submit]').isDisabled();
// Negative test: bypass the disabled UI to confirm the server also fails safely.
await Promise.all([page.waitForNavigation(),page.locator('#smile-form').evaluate(form=>form.requestSubmit())]);
const result={status:'missing_service_or_gd_graceful_failure',errorCount:await page.locator('[data-errors]').count(),
    unavailableButtonDisabled,stayedOnForm:new URL(page.url()).pathname==='/smile-ai',javascriptErrors:errors};
result.passed=result.errorCount===1&&result.unavailableButtonDisabled&&result.stayedOnForm&&!errors.length;
fs.mkdirSync('output/recovery-evidence',{recursive:true});
fs.writeFileSync('output/recovery-evidence/ai-browser.json',JSON.stringify(result,null,2));
await browser.close();console.log(JSON.stringify(result,null,2));if(!result.passed)process.exitCode=1;
