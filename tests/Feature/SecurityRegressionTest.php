<?php

namespace Tests\Feature;

use App\Models\Appointments;
use App\Models\AppointmentTypes;
use App\Models\DoctorAppointmentTypes;
use App\Models\DoctorProfile;
use App\Models\Specialities;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        // SQLite in memory; under NO circumstances use the team's MySQL DB here.
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
            'session.driver' => 'array',
            'cache.default' => 'array',
            'mail.default' => 'array',
        ]);
        DB::purge('sqlite');
    }

    public function test_suspended_account_cannot_reuse_preexisting_session(): void
    {
        $patient = User::factory()->create(['role' => 'patient', 'is_active' => true, 'email_verified_at' => now()]);
        $this->actingAs($patient)->get(route('patient.dashboard'))->assertOk();
        $patient->is_active = false;
        $patient->save();
        $this->get(route('patient.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_suspended_doctor_profile_cannot_use_doctor_workspace(): void
    {
        $doctor = User::factory()->create(['role' => 'doctor', 'is_active' => true]);
        $speciality = Specialities::create(['name' => 'Local security regression']);
        $profile = $doctor->doctor_profile()->create([
            'speciality_id' => $speciality->id,
            'license_number' => 'SECURITY-DOCTOR-001',
            'bio' => 'Isolated fixture',
            'consultation_fee' => 20,
        ]);
        $profile->is_active = false;
        $profile->save();
        $this->actingAs($doctor->fresh())->get(route('doctor.dashboard'))->assertForbidden();
    }

    public function test_patient_cannot_book_with_inactive_doctor_even_via_direct_post(): void
    {
        $patient = User::factory()->create(['role' => 'patient', 'email_verified_at' => now(), 'is_active' => true]);
        $doctor = User::factory()->create(['role' => 'doctor', 'is_active' => true]);
        $speciality = Specialities::create(['name' => 'Booking gate regression']);
        $profile = $doctor->doctor_profile()->create([
            'speciality_id' => $speciality->id,
            'license_number' => 'SECURITY-DOCTOR-002',
            'bio' => 'Isolated fixture',
            'consultation_fee' => 20,
        ]);
        $type = AppointmentTypes::create(['name' => 'Security consultation']);
        $service = $profile->doctor_appointment_types()->create([
            'appointment_type_id' => $type->id,
            'duration_minutes' => 30,
            'price' => 20,
        ]);
        $profile->is_active = false;
        $profile->save();
        $this->actingAs($patient)->post(route('patient.appointments.store'), [
            'doctor_appointment_type_id' => $service->id,
            'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
        ])->assertSessionHasErrors('doctor_appointment_type_id');
        $this->assertSame(0, Appointments::count());
    }

    public function test_smile_simulation_requires_verified_patient(): void
    {
        $this->post(route('smile.ai.generate'))->assertRedirect(route('login'));
        $doctor = User::factory()->create(['role' => 'doctor', 'is_active' => true]);
        $this->actingAs($doctor)->post(route('smile.ai.generate'))->assertForbidden();
        $patient = User::factory()->unverified()->create(['role' => 'patient', 'is_active' => true]);
        $this->actingAs($patient)->post(route('smile.ai.generate'))->assertRedirect(route('verification.notice'));
    }
}
