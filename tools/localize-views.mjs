import fs from 'node:fs';import path from 'node:path';
function walk(d){return fs.readdirSync(d,{withFileTypes:true}).flatMap(e=>e.isDirectory()?walk(path.join(d,e.name)):[path.join(d,e.name)]);}
const files=walk('resources/views').filter(f=>!/(welcome|home|contact|components[\\/]navbar|components[\\/]footer|smile-simulation[\\/]create)\.blade/.test(f));
const candidates=new Set();
const php=s=>"'"+s.replaceAll('\\','\\\\').replaceAll("'","\\'")+"'";
function add(s){s=s.trim().replace(/\s+/g,' ');if(/[A-Za-z\u0600-\u06ff]/.test(s)&&s.length>1)candidates.add(s);return s;}
function scan(s,fn){
 const protectedParts=[];const mask=v=>{const id=protectedParts.push(v)-1;return `\u0001${id}\u0002`;};
 s=s.replace(/{{--[\s\S]*?--}}|@php\b[\s\S]*?@endphp|<script\b[\s\S]*?<\/script>|<style\b[\s\S]*?<\/style>|{{[\s\S]*?}}|{!![\s\S]*?!!}/g,mask);
 s=s.replace(/@(\w+)([ \t]*\()?/g,(all,word,open,offset)=>all); // Balanced Blade directives below.
 let out='';for(let i=0;i<s.length;){if(s[i]==='@'&&/[A-Za-z]/.test(s[i+1]||'')){let j=i+1;while(/[\w]/.test(s[j]||''))j++;while(s[j]===' '||s[j]==='\t')j++;if(s[j]==='('){let depth=1,quote=null;j++;while(j<s.length&&depth){const ch=s[j++];if(quote){if(ch==='\\')j++;else if(ch===quote)quote=null;}else if(ch==='"'||ch==="'")quote=ch;else if(ch==='(')depth++;else if(ch===')')depth--;}}out+=mask(s.slice(i,j));i=j;}else out+=s[i++];}s=out;
 s=s.split(/(<(?:[^>"'\u0001]|"[^"]*"|'[^']*'|\u0001\d+\u0002)+>|\u0001\d+\u0002)/g).map(part=>{
  if(part.startsWith('<'))return part.replace(/(?<!:)\b(title|subtitle|label|hint|placeholder|alt|aria-label)="([^"\u0001]*)"/g,(all,key,val)=>{if(val.includes('$')||!val.trim()||!/[A-Za-z\u0600-\u06ff]/.test(val)||key==='title'&&val.includes('@'))return all;add(val);return fn?`${key}="{{ __(${php(val)}) }}"`:all;});
  if(part.startsWith('\u0001')||!part.trim()||!/[A-Za-z\u0600-\u06ff]/.test(part))return part;
  const key=add(part);return fn?part.replace(part.trim(),`{{ __(${php(key)}) }}`):part;
 }).join('');
 return s.replace(/\u0001(\d+)\u0002/g,(_,i)=>protectedParts[Number(i)]);
}
for(const f of files){let s=fs.readFileSync(f,'utf8');if(process.argv.includes('--write'))fs.writeFileSync(f,scan(s,true));else scan(s,false);}
fs.writeFileSync('output/ui-strings.json',JSON.stringify([...candidates].sort(),null,2));
console.log([...candidates].sort().join('\n'));

