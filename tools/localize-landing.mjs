import fs from 'node:fs';
const ar=JSON.parse(fs.readFileSync('lang/ar.json','utf8')),en=JSON.parse(fs.readFileSync('lang/en.json','utf8'));
Object.assign(ar,{
'Clinicians guide consultations, explain options and plan appropriate care.':'يوجه الأطباء الاستشارات ويشرحون الخيارات ويخططون للرعاية المناسبة.',
'Clinical support teams help prepare a calm and organized treatment experience.':'تساعد فرق الدعم السريري في تهيئة تجربة علاج هادئة ومنظمة.',
'Care coordinators support scheduling and the patient journey.':'يساعد منسقو الرعاية في تنظيم المواعيد ورحلة المريض.',
'Explore clinicians through their real profiles after the clinic directory is connected.':'تعرّف على الأطباء من خلال ملفاتهم الفعلية في دليل العيادة.',
'Choose a genuinely available date and time through the existing patient-authenticated booking flow.':'اختر تاريخاً ووقتاً متاحين عبر حساب المريض ونظام الحجز الفعلي.',
'01 / DISCOLORED ENAMEL · CONCEPT':'01 / مينا متصبغة · تصور توضيحي','02 / SIMULATED CLEANING SCAN':'02 / محاكاة مسح التنظيف','03 / BRIGHT ENAMEL · CONCEPT':'03 / مينا مشرقة · تصور توضيحي','02 / SCAN':'02 / المسح','03 / ANATOMY':'03 / التشريح','04 / SERVICES':'04 / الخدمات',
'Tooth orientation reset.':'تمت إعادة اتجاه السن.','Reduced-motion summary is available below the static scene.':'يتوفر ملخص بحركة مخففة أسفل المشهد الثابت.','Interactive tooth model loaded. Drag or use arrow keys to rotate.':'تم تحميل نموذج السن التفاعلي. اسحب أو استخدم مفاتيح الأسهم للتدوير.','Static tooth preview is displayed because 3D rendering is unavailable.':'تُعرض صورة ثابتة للسن لأن العرض ثلاثي الأبعاد غير متاح.','3D context lost; static illustration shown.':'تعذر العرض ثلاثي الأبعاد؛ تُعرض صورة ثابتة.',
'Appointment booked successfully.':'تم تسجيل طلب الموعد بنجاح.','Appointment cancelled successfully.':'تم إلغاء الموعد بنجاح.','Appointment rescheduled successfully.':'تم تغيير وقت الموعد بنجاح.','Appointment status updated successfully.':'تم تحديث حالة الموعد بنجاح.',
'Doctor already has an appointment at this time.':'لدى الطبيب موعد آخر في هذا الوقت.','Doctor is not available at this time.':'الطبيب غير متاح في هذا الوقت.','Appointment time must be in the future.':'يجب أن يكون وقت الموعد في المستقبل.','This appointment cannot be cancelled.':'لا يمكن إلغاء هذا الموعد.','Past appointments cannot be cancelled.':'لا يمكن إلغاء المواعيد السابقة.','This appointment cannot be rescheduled.':'لا يمكن تغيير وقت هذا الموعد.','Past appointments cannot be rescheduled.':'لا يمكن تغيير وقت المواعيد السابقة.','This status change is not allowed.':'هذا التغيير في الحالة غير مسموح.',
'Unable to sign in. If you registered as a patient, verify your email first.':'تعذر تسجيل الدخول. إذا سجّلت كمريض، تحقّق من بريدك الإلكتروني أولاً.',
'Your email is verified. You can now log in.':'تم التحقق من بريدك الإلكتروني. يمكنك تسجيل الدخول الآن.',
'If an account exists for this email, you will receive a password reset link.':'إذا وُجد حساب بهذا البريد، فسيصلك رابط لاستعادة كلمة المرور.',
'Your password has been saved. You can now log in.':'تم حفظ كلمة المرور. يمكنك تسجيل الدخول الآن.',
'This password reset link is invalid or has expired. Please request a new one.':'رابط استعادة كلمة المرور غير صالح أو منتهي الصلاحية. اطلب رابطاً جديداً.',
'Patient updated successfully':'تم تحديث بيانات المريض بنجاح','Your profile has been updated.':'تم تحديث ملفك الشخصي.','User updated successfully':'تم تحديث المستخدم بنجاح',
'Appointment type created successfully.':'تمت إضافة نوع الموعد بنجاح.','Appointment type updated successfully.':'تم تحديث نوع الموعد بنجاح.','Appointment type deleted successfully.':'تم حذف نوع الموعد بنجاح.',
'Cannot delete this type because it is assigned to a doctor.':'لا يمكن حذف هذا النوع لأنه مسند إلى طبيب.','This period overlaps an existing schedule.':'تتداخل هذه الفترة مع فترة عمل موجودة.',
'Speciality added successfully':'تمت إضافة التخصص بنجاح','Speciality updated successfully':'تم تحديث التخصص بنجاح','Speciality deleted successfully':'تم حذف التخصص بنجاح',
'Simulation failed. Check Laravel log and Python terminal. / فشلت المعالجة، راجع سجل Laravel ونافذة Python.':'تعذرت محاكاة الصورة. يرجى المحاولة لاحقاً.',
'CLINIC ROLE':'دور في العيادة','The page could not be found.':'تعذر العثور على الصفحة.','Return to homepage':'العودة إلى الرئيسية'
});
for(const key of Object.keys(ar))if(!(key in en))en[key]=key;
// Keep approved English baseline text while Arabic supplies localized equivalents.
fs.writeFileSync('lang/ar.json',JSON.stringify(ar,null,2)+'\n');fs.writeFileSync('lang/en.json',JSON.stringify(en,null,2)+'\n');
fs.mkdirSync('public/taqweem-integration',{recursive:true});
for(const name of ['site.js','scan-engine.js']){
 let s=fs.readFileSync('public/taqweem-landing/js/'+name,'utf8');
 for(const key of Object.keys(ar).sort((a,b)=>b.length-a.length)){
  if(!/[A-Za-z]/.test(key))continue;
  for(const quote of ["'",'"']){const literal=quote+key.replaceAll('\\','\\\\').replaceAll(quote,'\\'+quote)+quote;s=s.split(literal).join(`(window.TaqweemLanding[${JSON.stringify(key)}] || ${JSON.stringify(key)})`);}
 }
 s=s.replace('`0${i+1} / CLINIC ROLE`','`0${i+1} / ${window.TaqweemLanding["CLINIC ROLE"] || "CLINIC ROLE"}`');
 fs.writeFileSync('public/taqweem-integration/'+name,s);
}
let p='resources/views/taqweem-landing.blade.php',s=fs.readFileSync(p,'utf8');
s=s.replace('<html lang="en">','<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === \'ar\' ? \'rtl\' : \'ltr\' }}">');
s=s.replace('<div class="header-right">','<div class="header-right"><x-language-switch/>');
s=s.replace('src="./js/site.js"','src="{{ asset(\'taqweem-integration/site.js\') }}"').replace('src="./js/scan-engine.js"','src="{{ asset(\'taqweem-integration/scan-engine.js\') }}"');
s=s.replace('</head>',`<link rel="stylesheet" href="{{ asset('css/landing-locale.css') }}"><script>window.TaqweemLanding = @json(json_decode(file_get_contents(lang_path(app()->getLocale().'.json')), true));</script></head>`);
fs.writeFileSync(p,s);
