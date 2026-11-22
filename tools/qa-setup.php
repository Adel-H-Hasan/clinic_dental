<?php
$dir=__DIR__.'/../output/qa-local';
if(file_exists($dir.'/runtime.json'))throw new RuntimeException('QA runtime already exists; do not overwrite');
@mkdir($dir.'/sessions',0777,true);
$runtime=['database'=>'taqweem_qa_'.bin2hex(random_bytes(6)),'key'=>'base64:'.base64_encode(random_bytes(32)),'password'=>'Qa!'.bin2hex(random_bytes(8)).'aA1'];
$pdo=new PDO('mysql:host=127.0.0.1;port=3306','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE DATABASE `'.$runtime['database'].'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
file_put_contents($dir.'/runtime.json',json_encode($runtime));
$app=require __DIR__.'/qa-app.php';
Illuminate\Support\Facades\Artisan::call('migrate',['--force'=>true]);
foreach(['admin','doctor'] as $role){$u=new App\Models\User();$u->forceFill(['name'=>'QA '.ucfirst($role),'email'=>$role.'@taqweem.test','password'=>$runtime['password'],'phone'=>'+962790000001','role'=>$role,'email_verified_at'=>now(),'is_active'=>true]);$u->save();$users[$role]=$u;}
$speciality=App\Models\Specialities::create(['name'=>'QA General Dentistry','description'=>'Isolated QA fixture']);
$doctor=$users['doctor']->doctor_profile()->create(['speciality_id'=>$speciality->id,'license_number'=>'QA-ONLY','bio'=>'Isolated QA clinician fixture. Not a real clinic account.','consultation_fee'=>25,'years_experience'=>8]);
$type=App\Models\AppointmentTypes::create(['name'=>'QA Consultation','description'=>'Isolated QA service']);
$service=$doctor->doctor_appointment_types()->create(['appointment_type_id'=>$type->id,'duration_minutes'=>30,'price'=>25]);
foreach(range(0,6) as $day){$schedule=new App\Models\DoctorSchedules();$schedule->forceFill(['day_of_week'=>$day,'start_time'=>'09:00:00','end_time'=>'17:00:00','is_available'=>true]);$doctor->doctor_schedules()->save($schedule);}
file_put_contents($dir.'/fixture.json',json_encode(['service'=>$service->id,'doctor'=>$doctor->id,'date'=>now()->addDays(2)->format('Y-m-d')]));
echo "Created isolated QA database and staff/service fixtures. No team database touched.\n";
