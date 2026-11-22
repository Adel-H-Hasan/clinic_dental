import fs from 'node:fs';import crypto from 'node:crypto';import {spawnSync} from 'node:child_process';
const base='storage/app/recovery-20261122/baseline';
const paths=[];
for(const directory of ['app','bootstrap','config','routes','resources','lang','tests','tools']) {
 for(const file of fs.readdirSync(directory,{recursive:true}))if(fs.statSync(directory+'/'+file).isFile()&&!file.startsWith('cache'))paths.push(directory+'/'+file);
}
paths.push('medassist-ai-engine/app/main.py','medassist-ai-engine/run.ps1','medassist-ai-engine/requirements.txt','medassist-ai-engine/requirements-health.txt','medassist-ai-engine/README.md','medassist-ai-engine/tests/test_api.py');
const changed=[],added=[];let patch='';
for(const path of paths){
 const old=base+'/'+path;
 if(fs.existsSync(old)&&fs.readFileSync(path).equals(fs.readFileSync(old)))continue;
 (fs.existsSync(old)?changed:added).push(path.replaceAll('\\','/'));
 if(!path.endsWith('.png'))patch+=spawnSync('git',['diff','--no-index','--',fs.existsSync(old)?old:'NUL',path],{encoding:'utf8'}).stdout;
}
const hashes=JSON.parse(fs.readFileSync('storage/app/recovery-20261122/environment-hashes.json','utf8').replace(/^\uFEFF/,''));
const envUnchanged=hashes.every(x=>crypto.createHash('sha256').update(fs.readFileSync(x.Path)).digest('hex').toUpperCase()===x.Hash);
fs.mkdirSync('output/recovery-evidence',{recursive:true});
const email={};for(const variant of ['trace','plain']){
 const path=`output/qa-local/recovery-mail-${variant}.json`;
 email[variant]={...JSON.parse(fs.readFileSync(path)),recordedAtUtc:fs.statSync(path).mtime.toISOString(),inboxConfirmed:false};
}
fs.writeFileSync('output/recovery-evidence/email-traces.json',JSON.stringify(email,null,2));
fs.writeFileSync('docs/taqweem/RECOVERY_SOURCE.diff',patch);
const manifest={changed,added,envUnchanged,otherOutputs:['.gitignore','medassist-ai-engine/.venv/ (local runtime)','storage/app/recovery-20261122/ (local backup and Python runtime)','output/recovery-evidence/','docs/taqweem/RECOVERY_FINAL_AR.md','public/build/ (rebuilt)']};
fs.writeFileSync('docs/taqweem/RECOVERY_CHANGES.json',JSON.stringify(manifest,null,2));
console.log(JSON.stringify(manifest,null,2));if(!envUnchanged)process.exitCode=1;
