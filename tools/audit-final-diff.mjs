import fs from 'node:fs';import {spawnSync} from 'node:child_process';import crypto from 'node:crypto';
const baseline='storage/app/stabilization-20261121/baseline';
const mappings=['app','bootstrap','config','database','lang','resources','routes','tests','tools'].map(x=>[x,x]);
mappings.push(['css','public/css'],['js','public/js'],['taqweem-landing','public/taqweem-landing'],['taqweem-integration','public/taqweem-integration']);
const changed=[],added=[];let diff='';
for(const [oldDir,newDir] of mappings)for(const relative of fs.readdirSync(newDir,{recursive:true}).filter(x=>fs.statSync(newDir+'/'+x).isFile())){
 const path=newDir+'/'+relative,old=baseline+'/'+oldDir+'/'+relative;
 if(path.startsWith('bootstrap/cache/'))continue;
 if(!fs.existsSync(old)){added.push(path.replaceAll('\\','/'));diff+=spawnSync('git',['diff','--no-index','--','NUL',path],{encoding:'utf8'}).stdout;continue;}
 if(fs.readFileSync(old).equals(fs.readFileSync(path)))continue;
 changed.push(path.replaceAll('\\','/'));
 diff+=spawnSync('git',['diff','--no-index','--',old,path],{encoding:'utf8'}).stdout;
}
const initial=JSON.parse(fs.readFileSync('storage/app/stabilization-20261121/environment-hashes.json','utf8').replace(/^\uFEFF/,''));
const envUnchanged=initial.every(x=>crypto.createHash('sha256').update(fs.readFileSync(x.Path)).digest('hex').toUpperCase()===x.Hash);
const approved=['app/Http/Controllers/AuthController.php','app/Http/Controllers/PatientProfileController.php','bootstrap/app.php','routes/web.php','app/Http/Controllers/VerificationNotificationController.php'];
const unexpectedProtected=[...changed,...added].filter(p=>/^(app|bootstrap|config|database|routes)\//.test(p)&&!approved.includes(p));
const result={changed,added,envUnchanged,unexpectedProtected,gitRepository:false};
result.otherOutputs=['.gitignore','public/build/manifest.json','public/build/assets/*','docs/taqweem/STABILIZATION_FINAL_AR.md','docs/taqweem/EMAIL_DELIVERY_SETUP_AR.md','docs/taqweem/BACKEND_PENDING.diff','docs/taqweem/PROFILE_VERIFICATION_PENDING.diff'];
fs.writeFileSync('docs/taqweem/STABILIZATION_CHANGES.json',JSON.stringify(result,null,2));
fs.writeFileSync('docs/taqweem/STABILIZATION_SOURCE.diff',diff);
console.log(JSON.stringify(result,null,2));
if(!envUnchanged||unexpectedProtected.length)process.exitCode=1;
