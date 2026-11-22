import fs from 'node:fs';
const root = 'resources/views/';
for (const file of fs.readdirSync(root, {recursive:true}).filter(x=>x.endsWith('.blade.php'))) {
  const path = root + file;
  let s = fs.readFileSync(path,'utf8');
  if (s.includes('<x-workspace') || file.replaceAll('\\','/') === 'smile-simulation/ai-create.blade.php') {
    // The workspace already renders the complete validation summary exactly once.
    s = s.replace(/\s*@error\('[^']+'\)[\s\S]*?@enderror/g, '');
    s = s.replace(/\s*@if\s*\(\$errors->any\(\)\)[\s\S]*?@endif/g, '');
    s = s.replace(/\s*@foreach\s*\(\$errors->all\(\) as \$error\)[\s\S]*?@endforeach/g, '');
  }
  if(file.replaceAll('\\','/')==='smile-simulation/ai-create.blade.php') s=s.replace("<h1>{{ __('smile.title') }}</h1>",'');
  if (s!==fs.readFileSync(path,'utf8')) fs.writeFileSync(path,s);
}
const old='A calmer experience depends on the people you meet. These roles are illustrative; verified clinician profiles will be shown after database integration.';
const current='Meet the people behind your care. Explore our dentist directory for available clinician profiles and services.';
const verification='Open the verification message sent after registration and follow its secure link. Check your spam folder if it is not in your inbox.';
const description='TAQWEEM — Find your dentist, book appointments and follow your dental care.';
const path=root+'taqweem-landing.blade.php';
fs.writeFileSync(path,fs.readFileSync(path,'utf8').replace(old,current).replace('content="TAQWEEM — an immersive cinematic dental care experience, frontend concept."',`content="{{ __('${description}') }}"`));
for(const locale of ['ar','en']) {
  const path=`lang/${locale}.json`, messages=JSON.parse(fs.readFileSync(path,'utf8'));
  delete messages[old];
  messages[current]=locale==='ar'?'تعرّف على فريق رعايتك. استعرض دليل الأطباء للاطلاع على ملفات الأطباء والخدمات المتاحة.':current;
  messages[verification]=locale==='ar'?'افتح رسالة التحقق المرسلة بعد التسجيل واتبع رابطها الآمن. تحقّق من مجلد الرسائل غير المرغوب فيها إذا لم تجدها في الوارد.':verification;
  messages[description]=locale==='ar'?'تقويم — ابحث عن طبيبك واحجز المواعيد وتابع رعاية أسنانك.':description;
  fs.writeFileSync(path,JSON.stringify(messages,null,2)+'\n');
}
