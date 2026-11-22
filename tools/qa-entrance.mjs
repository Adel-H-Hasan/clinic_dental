import {chromium} from '@playwright/test';
import fs from 'node:fs';
const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox','--enable-unsafe-swiftshader']});
const results=[];
for(const width of [1440,768,390]) {
 const context=await browser.newContext({viewport:{width,height:900}}),page=await context.newPage(),errors=[];
 page.on('pageerror',e=>errors.push(e.message));
 await page.goto('http://127.0.0.1:8199/');
 await page.waitForFunction(()=>{const v=document.querySelector('#film-outside');return v.readyState>=2&&v.seekable.length&&v.seekable.end(0)>1;});
 for(const progress of [0,.30,.75,.30,0]) {
  await page.evaluate(p=>{const s=document.querySelector('#cinema');scrollTo({top:s.offsetTop+(s.offsetHeight-innerHeight)*p,behavior:'instant'});},progress);
  await page.waitForTimeout(1400);
  const state=await page.evaluate(()=>{const a=document.querySelector('#film-outside'),b=document.querySelector('#film-inside');return {outside:a.currentTime,inside:b.currentTime,seeking:a.seeking||b.seeking,ready:a.readyState,opacity:getComputedStyle(a).opacity,insideOpacity:getComputedStyle(b).opacity,poster:getComputedStyle(document.querySelector('#cinema-poster')).opacity,overflow:document.documentElement.scrollWidth>innerWidth+1};});
  const passed=!state.seeking&&!state.overflow&&state.ready>=2&&(progress===0?state.outside<.1:progress===.30?state.outside>1&&state.outside<3:state.inside>3);
  results.push({width,progress,...state,passed,errors:[...errors]});
  if(progress===.30)await page.screenshot({path:`output/qa-evidence/entrance-${width}.png`});
 }
 await page.emulateMedia({reducedMotion:'reduce'});
 await page.waitForTimeout(100);
 const reduced=await page.evaluate(()=>({video:getComputedStyle(document.querySelector('#film-outside')).display,poster:getComputedStyle(document.querySelector('#cinema-poster')).opacity}));
 results.push({width,reduced,passed:reduced.video==='none'&&reduced.poster==='1'});
 await context.close();
}
const context=await browser.newContext(),page=await context.newPage();
await page.route('**/entrance_complete_building_scrub.mp4',r=>r.abort());
await page.goto('http://127.0.0.1:8199/');await page.waitForTimeout(700);
results.push({fallback:true,passed:await page.locator('#cinema-poster').evaluate(el=>getComputedStyle(el).opacity==='1'&&el.naturalWidth>0)});
await browser.close();
fs.writeFileSync('output/qa-evidence/entrance-results.json',JSON.stringify(results,null,2));
console.log(JSON.stringify({checks:results.length,failures:results.filter(x=>!x.passed||x.errors?.length)},null,2));
if(results.some(x=>!x.passed||x.errors?.length))process.exitCode=1;
