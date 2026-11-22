import fs from 'node:fs';
let a=JSON.parse(fs.readFileSync('lang/ar.json','utf8'));a['Dental clinicians']='أطباء الأسنان';fs.writeFileSync('lang/ar.json',JSON.stringify(a,null,2)+'\n');
let p='resources/views/taqweem-landing.blade.php',s=fs.readFileSync(p,'utf8');s=s.replace('window.TaqweemLanding =',"document.cookie = 'taqweem_ui_locale=' + document.documentElement.lang + '; Path=/; SameSite=Lax';window.TaqweemLanding =");fs.writeFileSync(p,s);
p='tools/qa-responsive.mjs';s=fs.readFileSync(p,'utf8').replaceAll("'/admin/users/1'","'/admin/users/2'").replaceAll("'/admin/users/1/edit'","'/admin/users/2/edit'");fs.writeFileSync(p,s);
