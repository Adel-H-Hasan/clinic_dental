import fs from 'node:fs';import path from 'node:path';
const walk=d=>fs.readdirSync(d,{withFileTypes:true}).flatMap(e=>e.isDirectory()?walk(path.join(d,e.name)):[path.join(d,e.name)]);
for(const file of walk('resources/views')){
 let s=fs.readFileSync(file,'utf8');
 if(!/(components|patient|doctor|admin|auth|public|smile-simulation)/.test(file))continue;
 s=s.replaceAll('{{ $title }}','{{ __($title) }}').replaceAll('{{ $subtitle }}','{{ __($subtitle) }}').replaceAll('{{ $label }}','{{ __($label) }}').replaceAll('{{ $hint }}','{{ __($hint) }}');
 s=s.replaceAll("{{ $role ?: 'DENTAL CARE' }}","{{ __($role ?: 'DENTAL CARE') }}").replaceAll("{{ strtoupper($role ?: 'WELCOME') }}","{{ __($role ?: 'WELCOME') }}").replaceAll('{{ ucfirst($role) }}','{{ __($role) }}');
 s=s.replaceAll('{{ ucfirst($status) }}','{{ __(ucfirst($status)) }}').replaceAll('{{ ucfirst($appointment->status) }}','{{ __(ucfirst($appointment->status)) }}').replaceAll('{{ ucfirst($user->role) }}','{{ __($user->role) }}').replaceAll('{{ ucfirst($item->status) }}','{{ __(ucfirst($item->status)) }}');
 s=s.replaceAll('{{ $error }}','{{ __($error) }}').replaceAll('{{ $message }}','{{ __($message) }}').replaceAll('{{ session($key) }}','{{ __(session($key)) }}');
 s=s.replace(/->format\('([^']*[dMlF][^']*)'\)/g,"->locale(app()->getLocale())->translatedFormat('$1')");
 s=s.replace(/{{ (\$[\w?\->]+) \? 'Active' : 'Inactive' }}/g,"{{ __($1 ? 'Active' : 'Inactive') }}");
 s=s.replaceAll("{{ $user->email_verified_at ? 'Verified' : 'Not verified' }}","{{ __($user->email_verified_at ? 'Verified' : 'Not verified') }}");
 s=s.replaceAll("'Welcome, '.auth()->user()->name","__('Welcome, :name', ['name'=>auth()->user()->name])").replaceAll("'Welcome, Dr. '.$doctor->user->name","__('Welcome, Dr. :name', ['name'=>$doctor->user->name])").replaceAll("'Appointment #'.$appointment->id","__('Appointment #:id', ['id'=>$appointment->id])");
 s=s.replace(/confirm\('([^']+)'\)/g,(_,text)=>`confirm(@js(__('${text}')))`);
 fs.writeFileSync(file,s);
}
const file='resources/views/components/workspace.blade.php';let s=fs.readFileSync(file,'utf8');s=s.replace('<script src=',`<script>window.TaqweemUI = @json(['submitting'=>__('Submitting…'), 'invalidImage'=>__('Choose a JPEG, PNG or WebP image up to 8 MB'), 'processing'=>__('Processing securely…')]);</script><script src=`);fs.writeFileSync(file,s);
const auth='resources/views/components/auth-shell.blade.php';s=fs.readFileSync(auth,'utf8').replace('<script src=',`<script>window.TaqweemUI = @json(['submitting'=>__('Submitting…')]);</script><script src=`);fs.writeFileSync(auth,s);
const js='public/js/workspace.js';s=fs.readFileSync(js,'utf8').replace("button.textContent = 'Submitting…'","button.textContent = window.TaqweemUI?.submitting || button.textContent");fs.writeFileSync(js,s);
const smile='public/js/smile-ai.js';s=fs.readFileSync(smile,'utf8').replace("'Choose a JPEG, PNG or WebP image up to 8 MB'",'window.TaqweemUI.invalidImage').replace("'Processing securely… / جارٍ معالجة الصورة…'",'window.TaqweemUI.processing');fs.writeFileSync(smile,s);
