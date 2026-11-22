<?php
$dir=__DIR__.'/../output/demo-local';
foreach(['sessions','cache'] as $folder)if(!is_dir($dir.'/'.$folder))mkdir($dir.'/'.$folder,0700,true);
if(!is_file($dir.'/runtime.json')){
 $runtime=['database'=>'taqweem_demo_'.bin2hex(random_bytes(6)),'key'=>'base64:'.base64_encode(random_bytes(32))];
 $pdo=new PDO('mysql:host=127.0.0.1;port=3306','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
 $pdo->exec('CREATE DATABASE `'.$runtime['database'].'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
 file_put_contents($dir.'/runtime.json',json_encode($runtime));
}
$app=require __DIR__.'/demo-app.php';
Illuminate\Support\Facades\Artisan::call('migrate',['--force'=>true]);
if(is_file($dir.'/seeded.json')){echo "بيئة الاختبار موجودة؛ حُفظت التغييرات التي أجريتها عليها.\n";exit;}
$summary=Illuminate\Support\Facades\DB::transaction(function(){
 $users=[];$password='Taqweem!2026Demo';
 foreach([
 ['admin','مدير الاختبار','admin'],['doctor','د. سارة — بيانات وهمية','doctor'],
 ['doctor2','د. عمر — بيانات وهمية','doctor'],['doctor3','د. ليلى — بيانات وهمية','doctor'],
 ['patient','أحمد — مريض وهمي','patient'],['patient2','نور — مريضة وهمية','patient'],['patient3','كريم — مريض وهمي','patient'],
 ] as [$handle,$name,$role]){
  $u=new App\Models\User();$u->forceFill(['name'=>$name,'email'=>$handle.'@demo.test','password'=>$password,
   'phone'=>'+962790000001','role'=>$role,'gender'=>$handle==='patient2'?'female':'male',
   'date_of_birth'=>'1995-05-15','is_active'=>true,'email_verified_at'=>now()]);$u->save();$users[$handle]=$u;
 }
 $services=[];$doctors=[];
 foreach(['أسنان عامة — اختبار','تقويم أسنان — اختبار','تجميل أسنان — اختبار'] as $i=>$name){
  $speciality=App\Models\Specialities::create(['name'=>$name,'description'=>'تصنيف وهمي لاختبار الواجهات']);
  $handle=$i===0?'doctor':'doctor'.($i+1);
  $doctor=$users[$handle]->doctor_profile()->create(['speciality_id'=>$speciality->id,'license_number'=>'DEMO-'.($i+1),
   'bio'=>'ملف طبيب وهمي مخصص للاختبار فقط، ولا يمثل طبيباً أو خدمة طبية حقيقية.','consultation_fee'=>20+10*$i,'years_experience'=>5+$i]);
  $doctors[]=$doctor;
  foreach([['استشارة — اختبار',30,20],['تنظيف — اختبار',45,35]] as [$title,$minutes,$price]){
   $type=App\Models\AppointmentTypes::firstOrCreate(['name'=>$title],['description'=>'خدمة اصطناعية للاختبار']);
   $services[$i][]=$doctor->doctor_appointment_types()->create(['appointment_type_id'=>$type->id,'duration_minutes'=>$minutes,'price'=>$price]);
  }
  foreach(range(0,6)as$day){$schedule=new App\Models\DoctorSchedules();$schedule->forceFill(['day_of_week'=>$day,'start_time'=>'09:00:00','end_time'=>'17:00:00','is_available'=>true]);$doctor->doctor_schedules()->save($schedule);}
 }
 $counts=['users'=>7,'doctors'=>3,'services'=>6,'appointments'=>0,'notes'=>0,'reports'=>0];
 foreach($doctors as $i=>$doctor){
  foreach(['pending','confirmed','cancelled','completed','completed'] as $j=>$status){
   $patient=$users[$j%3===0?'patient':'patient'.($j%3+1)];
   $date=($status==='completed'?now()->subDays(3+$j):now()->addDays(1+$j))->setTime(10+$i,0);
   $visit=App\Models\Appointments::create(['patient_id'=>$patient->id,'doctor_appointment_type_id'=>$services[$i][0]->id,
    'scheduled_at'=>$date,'status'=>$status,'reason'=>'موعد وهمي لاختبار النظام فقط']);$counts['appointments']++;
   if($status==='completed'){
    $note=(new App\Models\MedicalNotes())->forceFill(['appointment_id'=>$visit->id,'doctor_profile_id'=>$doctor->id,
     'diagnosis'=>'مثال توثيق اصطناعي؛ ليس تشخيصاً طبياً.','notes'=>'ملاحظة وهمية لفحص التعديل والعرض والصلاحيات.',
     'prescription'=>null,'next_visit'=>now()->addDays(14)]);$note->save();$counts['notes']++;
    $report=(new App\Models\MedicalReport())->forceFill(['medical_note_id'=>$note->id,
     'content'=>'تقرير اختبار مكتوب مسبقاً. ليس نتيجة ذكاء اصطناعي ولا تقريراً طبياً حقيقياً.',
     'status'=>$j===3?'approved':'draft','approved_at'=>$j===3?now():null]);$report->save();$counts['reports']++;
   }
  }
 }
 return $counts;
});
file_put_contents($dir.'/seeded.json',json_encode($summary));
echo json_encode($summary,JSON_PRETTY_PRINT).PHP_EOL;
