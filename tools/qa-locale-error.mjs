import {chromium} from '@playwright/test';
import fs from 'node:fs';
const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox']});
const page=await browser.newPage();const results=[];
await page.goto('http://127.0.0.1:8097/login');
await page.locator('#email').fill('draft@taqweem.test');
await page.locator('[data-language-switch][lang=ar]').click();
results.push({test:'حفظ مسودة البريد أثناء تبديل اللغة',passed:await page.locator('#email').inputValue()==='draft@taqweem.test'&&await page.locator('html').getAttribute('lang')==='ar'});
await page.goto('http://127.0.0.1:8097/missing-qa-page');
for(const lang of ['en','ar']){await page.locator(`[data-language-switch][lang=${lang}]`).click();results.push({test:'تبديل لغة صفحة 404 إلى '+lang,passed:await page.locator('html').getAttribute('lang')===lang&&await page.locator(`[lang=${lang}][aria-current=true]`).count()===1});}
await browser.close();fs.writeFileSync('output/qa-evidence/error-locale-results.json',JSON.stringify(results,null,2));console.log(results);if(results.some(r=>!r.passed))process.exitCode=1;
