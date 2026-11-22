import fs from 'node:fs';import {chromium} from '@playwright/test';
const b=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox']});
const base='http://127.0.0.1:8199',runtime=JSON.parse(fs.readFileSync('output/qa-local/runtime.json')),patient=JSON.parse(fs.readFileSync('output/qa-local/patient.json'));const results=[];
function check(value,test){results.push({test,passed:!!value});if(!value)throw Error(test);}
try{for(const lang of ['ar','en'])for(const role of ['guest','patient','doctor','admin']){
const c=await b.newContext(),p=await c.newPage(),errors=[];p.on('pageerror',e=>errors.push(e.message));await p.goto(base+'/language/'+lang+'?return=%2F');
if(role!=='guest'){await p.goto(base+'/login');await p.locator('#email').fill(role==='patient'?patient.email:role+'@taqweem.test');await p.locator('#password').fill(runtime.password);await Promise.all([p.waitForURL('**/'+role+'/dashboard'),p.locator('button[type=submit]').click()]);check(true,`${lang}/${role}: الدخول إلى اللوحة الصحيحة`);await p.locator('a.brand:visible').first().click();check(new URL(p.url()).pathname==='/',`${lang}/${role}: العودة للرئيسية`);}
for(const width of [1440,390]){await p.setViewportSize({width,height:900});await p.goto(base+'/');await p.evaluate(()=>document.fonts.ready);if(width===390)await p.locator('#nav-toggle').click();
const links=await p.locator('#global-header a:visible').evaluateAll(els=>els.map(e=>({href:e.getAttribute('href'),lang:e.lang})));
for(const link of links){if(link.href.startsWith('#')){check(await p.locator(link.href).count()===1,`${lang}/${role}/${width}: مرساة ${link.href}`);continue;}if(link.lang)continue;
const response=await c.request.get(link.href);check(response.status()<400,`${lang}/${role}/${width}: رابط ${new URL(link.href).pathname}`);}
check(await p.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),`${lang}/${role}/${width}: دون تجاوز أفقي`);
await p.screenshot({path:`output/qa-evidence/navigation-${role}-${lang}-${width}.png`});
await p.goto(base+'/');await p.locator('.header-right .nav-book').click();check(new URL(p.url()).pathname===(role==='guest'?'/login':role==='patient'?'/patient/booking':role==='doctor'?'/doctor/workspace':'/admin/dashboard'),`${lang}/${role}/${width}: وجهة الحجز`);
}
await p.goto(base+'/');if(role==='guest'){
await p.locator('.header-right .nav-book').click();await p.locator('#email').fill(patient.email);await p.locator('#password').fill(runtime.password);await Promise.all([p.waitForURL('**/patient/booking'),p.locator('button[type=submit]').click()]);check(true,`${lang}: الحجز بعد مصادقة الضيف`);
}else{await p.locator('#nav-toggle').click();await Promise.all([p.waitForURL(base+'/'),p.locator('#mobile-nav form button').click()]);check(await p.locator('#mobile-nav a[href$="/login"]').count()===1,`${lang}/${role}: تسجيل الخروج وإعادة روابط الزائر`);check(await p.locator('html').getAttribute('lang')===lang,`${lang}/${role}: حفظ اللغة بعد الخروج`);}
check(!errors.length,`${lang}/${role}: دون أخطاء JavaScript`);await c.close();}
}catch(e){results.push({test:e.message,passed:false});process.exitCode=1;}finally{await b.close();fs.writeFileSync('output/qa-evidence/navigation-results.json',JSON.stringify(results,null,2));console.log(JSON.stringify({checks:results.length,failed:results.filter(r=>!r.passed)},null,2));}
