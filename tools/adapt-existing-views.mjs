import fs from 'node:fs';
import path from 'node:path';
function files(dir){return fs.readdirSync(dir,{withFileTypes:true}).flatMap(e=>e.isDirectory()?files(path.join(dir,e.name)):[path.join(dir,e.name)]);}
for(const f of ['admin','doctor','patient'].flatMap(d=>files('resources/views/'+d))){
 let s=fs.readFileSync(f,'utf8'); if(!s.includes('<body'))continue;
 const title=(s.match(/<title>(.*?)<\/title>/s)?.[1]||'Dental care').split('|')[0].trim();
 let body=s.match(/<body[^>]*>([\s\S]*?)<\/body>/)?.[1]||'';
 body=body.replace(/<main[^>]*>/g,'<div class="view-content">').replace(/<\/main>/g,'</div>').replace(/<h1[^>]*>[\s\S]*?<\/h1>/,'').replace(/<header class="report-brand">[\s\S]*?<\/header>/,'');
 body=body.replace(/<table([^>]*)>/g,'<div class="workspace-table" role="region" aria-label="Records" tabindex="0"><table$1>').replace(/<\/table>/g,'</table></div>');
 fs.writeFileSync(f,`<x-workspace title="${title.replaceAll('"','&quot;')}">\n<div class="panel">${body}\n</div>\n</x-workspace>\n`);
}
const auth={login:['Welcome back','Sign in to continue your dental care journey.'],register:['Create your account','Your dental care, all in one secure place.'],'forgot-password':['Forgot your password?','We will send you a secure recovery link.'],'reset-password':['Choose a new password','Use a unique password for your account.'],'verify-email':['Verify your email','Check your inbox to finish account setup.']};
for(const [name,[title,subtitle]] of Object.entries(auth)){
 const f=`resources/views/auth/${name}.blade.php`;let s=fs.readFileSync(f,'utf8');
 let body=s.match(/<body[^>]*>([\s\S]*?)<\/body>/)?.[1]||'';
 body=body.replace(/<\/?main[^>]*>/g,'').replace(/<h1[^>]*>[\s\S]*?<\/h1>/,'');
 if(name==='register'||name==='reset-password')body=body.replace('<form ', '<p class="notice">Use at least 8 characters, including uppercase, lowercase, a number, and a symbol. Phone numbers must include the country code, for example +962.</p><form ');
 fs.writeFileSync(f,`<x-auth-shell title="${title}" subtitle="${subtitle}">\n${body}\n</x-auth-shell>\n`);
}
