<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\User;
use App\Models\Appointments;
use App\Models\MedicalNotes;
use App\Models\MedicalReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class TaqweemIntegrationTest extends TestCase
{
    private array $fixture;
    protected function setUp(): void
    {
        parent::setUp();
        $path=base_path('output/qa-local/runtime.json');
        if(!is_file($path))$this->markTestSkipped('Run php tools/qa-setup.php to create an isolated QA database.');
        $runtime=json_decode(file_get_contents($path),true);
        if(!preg_match('/^taqweem_qa_[a-f0-9]{12}$/',$runtime['database']))throw new \RuntimeException('Unsafe test database');
        config(['app.key'=>$runtime['key'],'database.default'=>'mysql','database.connections.mysql.database'=>$runtime['database'],'database.connections.mysql.host'=>'127.0.0.1','database.connections.mysql.username'=>'root','database.connections.mysql.password'=>'','database.connections.mysql.url'=>null,'session.driver'=>'array','cache.default'=>'array','mail.default'=>'array','services.gemini.demo_review'=>false,'services.medassist_ai.token'=>null]);
        DB::purge('mysql');DB::beginTransaction();
        $this->fixture=json_decode(file_get_contents(base_path('output/qa-local/fixture.json')),true);
    }
    protected function tearDown():void { if(isset($this->fixture)) DB::rollBack(); parent::tearDown(); }
    private function patient():User {return User::factory()->create(['phone'=>'+962790000003','role'=>'patient']);}
    private function doctor():User {return User::where('email','doctor@taqweem.test')->firstOrFail();}
    private function booking(User $patient,string $time='10:00'):Appointments {
        $when=now()->addDays(5)->format('Y-m-d').' '.$time;
        $this->actingAs($patient)->from('/patient/booking/review')->post(route('patient.appointments.store'),['doctor_appointment_type_id'=>$this->fixture['service'],'scheduled_at'=>$when,'reason'=>'QA isolated'])->assertSessionHasNoErrors()->assertRedirect('/patient/booking/review');
        return Appointments::where('patient_id',$patient->id)->latest('id')->firstOrFail();
    }
    public function test_booking_conflicts_reschedule_cancel_and_ownership():void {
        $a=$this->patient();$b=$this->patient();$visit=$this->booking($a);
        $this->actingAs($b)->post(route('patient.appointments.store'),['doctor_appointment_type_id'=>$this->fixture['service'],'scheduled_at'=>$visit->scheduled_at->format('Y-m-d H:i')])->assertSessionHasErrors('scheduled_at');
        $this->actingAs($b)->get(route('patient.visits.show',$visit->id))->assertNotFound();
        $this->actingAs($b)->patch(route('patient.appointments.cancel',$visit->id))->assertNotFound();
        $this->actingAs($a)->patch(route('patient.appointments.reschedule',$visit->id),['scheduled_at'=>$visit->scheduled_at->copy()->addHour()->format('Y-m-d H:i')])->assertSessionHasNoErrors();
        $this->assertSame('11:00',$visit->fresh()->scheduled_at->format('H:i'));
        $this->patch(route('patient.appointments.cancel',$visit->id))->assertSessionHasNoErrors();$this->assertSame('cancelled',$visit->fresh()->status);
    }
    public function test_doctor_confirmation_and_invalid_early_completion():void {
        $patient=$this->patient();$visit=$this->booking($patient);
        $this->actingAs($this->doctor())->patch(route('doctor.appointments.status',$visit->id),['status'=>'confirmed'])->assertSessionHasNoErrors();
        $this->assertSame('confirmed',$visit->fresh()->status);
        $this->patch(route('doctor.appointments.status',$visit->id),['status'=>'completed'])->assertSessionHasErrors('status');
        $this->actingAs($patient)->getJson(route('patient.appointments.show',$visit->id))->assertOk()->assertJsonPath('status','confirmed');
    }
    public function test_clinical_notes_and_approved_report_permissions():void {
        $patient=$this->patient();$visit=Appointments::create(['patient_id'=>$patient->id,'doctor_appointment_type_id'=>$this->fixture['service'],'scheduled_at'=>now()->subDays(2),'status'=>'confirmed']);
        $this->actingAs($this->doctor())->patch(route('doctor.appointments.status',$visit->id),['status'=>'completed'])->assertSessionHasNoErrors();
        $this->post(route('doctor.appointments.medical-notes.store',$visit->id),['diagnosis'=>'نص طبي أصلي QA','notes'=>'Original record'])->assertRedirect();
        $note=MedicalNotes::where('appointment_id',$visit->id)->firstOrFail();
        $report=MedicalReport::create(['medical_note_id'=>$note->id,'content'=>'QA clinician draft']);
        $this->actingAs($patient)->get(route('patient.reports.show',$report->id))->assertNotFound();
        $this->actingAs($this->doctor())->post(route('doctor.medical-notes.report.approve',$note->id),['content'=>'QA approved report'])->assertRedirect();
        $this->actingAs($patient)->withSession(['locale'=>'ar'])->get(route('patient.reports.show',$report->id))->assertOk()->assertSee('QA approved report');
        $this->actingAs($this->patient())->get(route('patient.reports.show',$report->id))->assertNotFound();
        $this->assertSame('نص طبي أصلي QA',$note->fresh()->diagnosis);
    }
    public function test_language_return_logout_and_role_guards():void {
        $patient=$this->patient();
        $this->actingAs($patient)->get('/language/ar?return=%2Fpatient%2Fdashboard')->assertRedirect('/patient/dashboard')->assertSessionHas('locale','ar');
        $this->get('/patient/dashboard')->assertOk()->assertSee('dir="rtl"',false);
        $this->get('/admin/dashboard')->assertForbidden();$this->get('/doctor/workspace')->assertForbidden();
        $this->post('/logout')->assertRedirect('/')->assertSessionHas('locale','ar');$this->assertGuest();
        $this->get('/patient/dashboard')->assertRedirect('/login');
        $this->get('/language/en?return=https://example.org')->assertRedirect('/');
        $this->get('/language/fr')->assertNotFound();
    }
    public function test_admin_type_crud_and_patient_rejection():void {
        $admin=User::where('role','admin')->firstOrFail();$this->actingAs($admin);
        $this->post(route('admin.appointment-types.store'),['name'=>'QA regression type'])->assertSessionHasNoErrors();
        $type=\App\Models\AppointmentTypes::where('name','QA regression type')->firstOrFail();
        $this->put(route('admin.appointment-types.update',$type->id),['name'=>'QA renamed'])->assertSessionHasNoErrors();
        $this->delete(route('admin.appointment-types.destroy',$type->id))->assertSessionHasNoErrors();$this->assertDatabaseMissing('appointment_types',['id'=>$type->id]);
        $this->actingAs($this->patient())->post(route('admin.appointment-types.store'),['name'=>'Not allowed'])->assertForbidden();
    }
    public function test_smile_invalid_upload_and_private_result_access():void {
        $this->actingAs($this->patient());
        $this->post('/smile-ai/generate',['image'=>UploadedFile::fake()->create('invalid.txt',1,'text/plain'),'mode'=>'mouth','strength'=>'natural','treatment'=>'whitening'])->assertSessionHasErrors(['image','consent']);
        $this->get('/smile-ai/result/00000000-0000-0000-0000-000000000000')->assertNotFound();
        $this->withSession(['locale'=>'ar'])->get('/smile-ai')->assertOk()->assertSee('محاكاة الابتسامة غير متاحة');
    }
    public function test_existing_profile_edit_is_scoped_to_second_doctor():void {
        $doctor=User::factory()->create(['role'=>'doctor']);$original=$this->doctor()->doctor_profile;
        $profile=$doctor->doctor_profile()->create(['speciality_id'=>$original->speciality_id,'license_number'=>'QA-SECOND','bio'=>'SECOND-DOCTOR-PRIVATE','consultation_fee'=>25]);
        $this->actingAs($doctor)->get(route('doctor.profile.edit'))->assertOk()->assertSee('SECOND-DOCTOR-PRIVATE')->assertDontSee($original->bio);
    }
    public function test_admin_doctor_speciality_and_user_management():void {
        $this->actingAs(User::where('role','admin')->firstOrFail());
        $this->post(route('admin.specialities.store'),['name'=>'QA specialist','description'=>'Test'])->assertSessionHasNoErrors();
        $speciality=\App\Models\Specialities::where('name','QA specialist')->firstOrFail();
        $this->put(route('admin.specialities.update',$speciality->id),['name'=>'QA specialist revised','description'=>'Test revised'])->assertSessionHasNoErrors();
        $this->post(route('admin.doctors.store'),['name'=>'QA New Doctor','email'=>'new-doctor-'.uniqid().'@taqweem.test','phone'=>'+962790000004','speciality_id'=>$speciality->id,'license_number'=>'QA-'.uniqid(),'bio'=>'QA bio','consultation_fee'=>'25.00'])->assertSessionHasNoErrors();
        $doctor=\App\Models\DoctorProfile::where('speciality_id',$speciality->id)->firstOrFail();
        $this->put(route('admin.doctors.update',$doctor->id),['speciality_id'=>$speciality->id,'license_number'=>$doctor->license_number,'bio'=>'Updated QA bio','consultation_fee'=>'30.00','years_experience'=>5,'is_active'=>1])->assertSessionHasNoErrors();
        $this->assertSame('Updated QA bio',$doctor->fresh()->bio);
        $this->put(route('admin.users.update',$doctor->user_id),['is_active'=>0])->assertSessionHasNoErrors();$this->assertFalse((bool)$doctor->user->fresh()->is_active);
        $this->delete(route('admin.doctors.destroy',$doctor->id))->assertSessionHasNoErrors();$this->assertFalse((bool)$doctor->fresh()->is_active);
        $unused=\App\Models\Specialities::create(['name'=>'QA unused']);$this->delete(route('admin.specialities.destroy',$unused->id))->assertRedirect();$this->assertDatabaseMissing('specialities',['id'=>$unused->id]);
    }
    public function test_doctor_schedule_service_and_profile_forms():void {
        $doctor=$this->doctor();$this->actingAs($doctor);
        $this->post(route('doctor.schedules.store'),['day_of_week'=>1,'start_time'=>'18:00','end_time'=>'20:00'])->assertSessionHasNoErrors();
        $schedule=$doctor->doctor_profile->doctor_schedules()->where('start_time','18:00:00')->firstOrFail();
        $this->put(route('doctor.schedules.update',$schedule->id),['day_of_week'=>1,'start_time'=>'18:30','end_time'=>'20:00','is_available'=>1])->assertSessionHasNoErrors();
        $this->delete(route('doctor.schedules.destroy',$schedule->id))->assertRedirect();$this->assertDatabaseMissing('doctor_schedules',['id'=>$schedule->id]);
        $type=\App\Models\AppointmentTypes::create(['name'=>'QA additional service']);
        $this->post(route('doctor.services.store'),['appointment_type_id'=>$type->id,'duration_minutes'=>45,'price'=>'30.00'])->assertSessionHasNoErrors();
        $service=$doctor->doctor_profile->doctor_appointment_types()->where('appointment_type_id',$type->id)->firstOrFail();
        $this->put(route('doctor.services.update',$service->id),['duration_minutes'=>60,'price'=>'35.00'])->assertSessionHasNoErrors();
        $this->delete(route('doctor.services.destroy',$service->id))->assertRedirect();$this->assertDatabaseMissing('doctor_appointment_types',['id'=>$service->id]);
        $this->patch(route('doctor.profile.update'),['bio'=>'Updated isolated QA bio','years_experience'=>9])->assertSessionHasNoErrors();
        $this->assertSame('Updated isolated QA bio',$doctor->doctor_profile->fresh()->bio);
    }
    public function test_password_reset_and_patient_profile():void {
        $patient=$this->patient();$token=\Illuminate\Support\Facades\Password::createToken($patient);
        $password='QaNew!2026Secure';
        $this->post(route('password.update'),['email'=>$patient->email,'token'=>$token,'password'=>$password,'password_confirmation'=>$password])->assertRedirect(route('login'));
        $this->post(route('login'),['email'=>$patient->email,'password'=>$password])->assertRedirect(route('patient.dashboard'));$this->assertAuthenticatedAs($patient);
        $this->patch(route('patient.profile.update'),['name'=>'Updated QA patient','email'=>$patient->email,'phone'=>'+962790000005','gender'=>'female'])->assertSessionHasNoErrors();
        $this->assertSame('Updated QA patient',$patient->fresh()->name);
        $this->post(route('logout.submit'))->assertRedirect('/');$this->assertGuest();
    }

    public function test_ai_generated_report_requires_owner_approval_and_is_escaped():void {
        config(['services.gemini.demo_review'=>true]);
        $patient=$this->patient();$doctor=$this->doctor();
        $visit=Appointments::create(['patient_id'=>$patient->id,'doctor_appointment_type_id'=>$this->fixture['service'],'scheduled_at'=>now()->subDays(2),'status'=>'completed']);
        $note=(new MedicalNotes())->forceFill(['appointment_id'=>$visit->id,'doctor_profile_id'=>$doctor->doctor_profile->id,'diagnosis'=>'Synthetic clinical note']);$note->save();
        $this->mock(\App\Services\GeminiClient::class,fn($m)=>$m->shouldReceive('generateJson')->once()->andReturn(['report'=>'<script>synthetic()</script> مسودة اصطناعية']));
        $this->actingAs($doctor)->post(route('doctor.medical-notes.report.store',$note->id))->assertRedirect();
        $report=$note->medical_report()->firstOrFail();$this->assertSame('draft',$report->status);
        $this->actingAs($patient)->get(route('patient.reports.show',$report->id))->assertNotFound();
        $this->actingAs($doctor)->get(route('doctor.medical-notes.report.review',$note->id))->assertOk()->assertDontSee('<script>synthetic()</script>',false);
        $other=User::factory()->create(['role'=>'doctor']);$other->doctor_profile()->create(['speciality_id'=>$doctor->doctor_profile->speciality_id,'license_number'=>'QA-REPORT-OTHER','bio'=>'Synthetic profile','consultation_fee'=>25]);
        $this->actingAs($other)->post(route('doctor.medical-notes.report.approve',$note->id),['content'=>'Not owner'])->assertNotFound();
        $this->actingAs($doctor)->post(route('doctor.medical-notes.report.approve',$note->id),['content'=>'Approved synthetic draft'])->assertRedirect();
        $this->actingAs($patient)->get(route('patient.reports.show',$report->id))->assertOk()->assertSee('Approved synthetic draft');
        $this->actingAs($this->patient())->get(route('patient.reports.show',$report->id))->assertNotFound();
    }

    public function test_legacy_image_upload_is_private_and_honestly_reports_no_processor():void {
        \Illuminate\Support\Facades\Storage::fake('local');
        $patient=$this->patient()->refresh();
        $image=UploadedFile::fake()->createWithContent('synthetic.png',file_get_contents(base_path('tests/Fixtures/synthetic-smile.png')));
        $response=$this->actingAs($patient)->postJson('/smile-simulation/upload',['before_image'=>$image])->assertCreated()->assertJsonPath('status','pending')->assertJsonPath('processing_available',false);
        $analysis=\App\Models\AiImageAnalyses::findOrFail($response->json('analysis_id'));
        $this->assertSame($patient->id,$analysis->user_id);
        \Illuminate\Support\Facades\Storage::disk('local')->assertExists($analysis->before_image);
        $patient->forceFill(['email_verified_at'=>null])->save();
        $this->actingAs($patient)->postJson('/smile-simulation/upload',['before_image'=>$image])->assertForbidden();
    }

}

