import {chromium} from '@playwright/test';import fs from 'node:fs';
const base='http://127.0.0.1:8200',password='Taqweem!2026Demo';
const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
const checks=[],errors=[];
const check=(ok,label)=>{checks.push({label,passed:!!ok});if(!ok)throw Error(label);};
try{
 for(const [role,paths]of Object.entries({patient:['/patient/dashboard','/dentists','/patient/booking','/patient/workspace','/patient/profile','/patient/profile/edit','/smile-ai'],doctor:['/doctor/dashboard','/doctor/workspace','/doctor/schedules','/doctor/services','/doctor/profile','/doctor/profile/edit'],admin:['/admin/dashboard','/admin/users','/admin/doctors','/admin/specialities','/admin/service-catalog']})){
  const context=await browser.newContext(),page=await context.newPage();page.on('pageerror',e=>errors.push(e.message));
  await page.goto(base+'/login');await page.locator('#email').fill(role+'@demo.test');await page.locator('#password').fill(password);
  await Promise.all([page.waitForURL('**/'+role+'/dashboard'),page.locator('button[type=submit]').click()]);check(true,'دخول '+role+' دون تحقق بريد');
  for(const path of paths){const response=await page.goto(base+path);check(response.status()===200,role+' '+path);}
  if(role==='patient')check((await page.goto(base+'/admin/dashboard')).status()===403,'رفض وصول المريض إلى الإدارة');
  await context.close();
 }
 const context=await browser.newContext(),page=await context.newPage();page.on('pageerror',e=>errors.push(e.message));
 await page.goto(base+'/register');const email='new-'+Date.now()+'@demo.test';
 await page.locator('#name').fill('حساب تسجيل وهمي');await page.locator('#email').fill(email);await page.locator('#phone').fill('+962790000099');
 await page.locator('#password').fill(password);await page.locator('#password_confirmation').fill(password);
 await Promise.all([page.waitForURL('**/login'),page.locator('button[type=submit]').click()]);check(true,'التسجيل الجديد يتجاوز انتظار البريد في بيئة Demo فقط');
 await page.locator('#email').fill(email);await page.locator('#password').fill(password);
 await Promise.all([page.waitForURL('**/patient/dashboard'),page.locator('button[type=submit]').click()]);check(true,'الدخول بالحساب الجديد');
 await page.goto(base+'/patient/booking');await page.locator('#service').selectOption({index:1});
 const date=new Date(Date.now()+3*86400000).toISOString().slice(0,10);await page.locator('#date').fill(date);
 await Promise.all([page.waitForNavigation(),page.locator('form[method=get] button').click()]);
 await page.locator('input[name=scheduled_at]').first().check();await page.locator('#reason').fill('اختبار حجز وهمي عبر المتصفح');
 await Promise.all([page.waitForNavigation(),page.locator('form[action$="/booking/review"] button').click()]);
 await Promise.all([page.waitForNavigation(),page.locator('form[action$="/patient/appointments"] button').click()]);
 await page.goto(base+'/patient/workspace');const href=await page.locator('a[href*="/patient/visits/"]').first().getAttribute('href');
 const id=href.split('/').at(-1);check(!!id,'حجز موعد عبر النموذج الحقيقي');
 const doctorContext=await browser.newContext(),doctorPage=await doctorContext.newPage();await doctorPage.goto(base+'/login');
 await doctorPage.locator('#email').fill('doctor@demo.test');await doctorPage.locator('#password').fill(password);
 await Promise.all([doctorPage.waitForURL('**/doctor/dashboard'),doctorPage.locator('button[type=submit]').click()]);
 await doctorPage.goto(base+'/doctor/workspace');
 await Promise.all([doctorPage.waitForNavigation(),doctorPage.locator(`form[action$="/appointments/${id}/status"] button`).click()]);
 await page.goto(base+'/patient/workspace');
 check(await page.locator(`tr:has(a[href$="/patient/visits/${id}"])`).innerText().then(t=>/Confirmed|مؤكد/.test(t)),'تأكيد الطبيب وظهور الحالة للمريض');
 await doctorContext.close();
 await page.goto(base+'/language/en?return=%2Fpatient%2Fdashboard');check(await page.locator('html').getAttribute('lang')==='en','تبديل اللغة');
 await context.close();check(errors.length===0,'دون أخطاء JavaScript');
}catch(e){checks.push({label:e.message,passed:false});process.exitCode=1;}
finally{await browser.close();fs.writeFileSync('output/demo-local/smoke-results.json',JSON.stringify({checks,errors},null,2));console.log(JSON.stringify({checks:checks.length,failures:checks.filter(x=>!x.passed),errors},null,2));}
