import {chromium} from '@playwright/test';import fs from 'node:fs';
const b=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});const rows=[];
for(const lang of ['en','ar'])for(const width of [1440,768,390,320]){
 const c=await b.newContext({viewport:{width,height:width===320?568:900}}),p=await c.newPage();
 await p.goto(`http://127.0.0.1:8199/language/${lang}?return=%2Flogin`);
 for(const route of ['/login','/register','/forgot-password','/reset-password/qa-invalid?email=qa%40example.test','/email/verify']){
  const response=await p.goto('http://127.0.0.1:8199'+route);await p.evaluate(()=>document.fonts.ready);
  const metrics=await p.evaluate(()=>({overflow:document.documentElement.scrollWidth>innerWidth+1,height:document.documentElement.scrollHeight,viewport:innerHeight,
   missingLabels:[...document.querySelectorAll('input:not([type=hidden]),select')].filter(x=>!x.labels?.length&&!x.getAttribute('aria-label')).length,
   headings:document.querySelectorAll('h1').length,font:getComputedStyle(document.body).fontFamily}));
  rows.push({lang,width,route,status:response.status(),...metrics,passed:response.ok()&&!metrics.overflow&&!metrics.missingLabels&&metrics.headings===1});
  if(['/login','/register'].includes(route)&&(width===1440||width===390))await p.screenshot({path:`output/qa-evidence/auth-${route.slice(1)}-${lang}-${width}.png`,fullPage:true});
 }
 await c.close();
}
await b.close();fs.writeFileSync('output/qa-evidence/auth-layout-results.json',JSON.stringify(rows,null,2));console.log(JSON.stringify({checks:rows.length,failures:rows.filter(x=>!x.passed)},null,2));
if(rows.some(x=>!x.passed))process.exitCode=1;
