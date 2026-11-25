<?php

namespace Database\Seeders;

use App\Models\Appointments;
use App\Models\AppointmentTypes;
use App\Models\DoctorAppointmentTypes;
use App\Models\DoctorProfile;
use App\Models\DoctorSchedules;
use App\Models\MedicalNotes;
use App\Models\MedicalReport;
use App\Models\Specialities;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Explicit, non-destructive local staging data for the existing `taqweem` DB.
 * NEVER invoke automatically from DatabaseSeeder or in any deployed environment.
 */
class TaqweemLocalSeeder extends Seeder
{
    private const TEST_PASSWORD = 'Taqweem!2026Demo';

    public function run(): void
    {
        $database = DB::connection()->getDatabaseName();
        if (!app()->environment('local') || config('database.default') !== 'mysql' || $database !== 'taqweem') {
            throw new \RuntimeException('Refusing to seed: requires APP_ENV=local and MySQL database exactly taqweem.');
        }

        // All accounts use reserved, non-deliverable .test email addresses.
        // These are training records, NOT real patients or licensed professionals.
        $summary = DB::transaction(function (): array {
            $people = [
                ['admin@taqweem.test', 'مدير العيادة', 'admin', 'male'],
                ['rana@taqweem.test', 'د. رنا الحسن', 'doctor', 'female'],
                ['yazan@taqweem.test', 'د. يزن الخطيب', 'doctor', 'male'],
                ['hiba@taqweem.test', 'د. هبة منصور', 'doctor', 'female'],
                ['ahmad@taqweem.test', 'أحمد العبدالله', 'patient', 'male'],
                ['layan@taqweem.test', 'ليان حمّاد', 'patient', 'female'],
                ['omar@taqweem.test', 'عمر النجار', 'patient', 'male'],
            ];
            $users = [];
            $phoneSuffix = 1;
            foreach ($people as [$email, $name, $role, $gender]) {
                $user = User::withTrashed()->where('email', $email)->first();
                if (!$user) {
                    do {
                        $phone = sprintf('+96279%07d', $phoneSuffix++);
                    } while (User::withTrashed()->where('phone', $phone)->exists());

                    $user = new User();
                    $user->forceFill([
                        'email' => $email,
                        'name' => $name,
                        'role' => $role,
                        'gender' => $gender,
                        'phone' => $phone,
                        'password' => self::TEST_PASSWORD,
                        'is_active' => true,
                        // Local test accounts don't depend on the external mail provider.
                        'email_verified_at' => now(),
                    ])->save();
                    $user = User::where('email', $email)->firstOrFail();
                } elseif ($user->trashed() || $user->role !== $role) {
                    throw new \RuntimeException('Conflicting existing test account: ' . $email);
                }
                // Preserve any existing account and its password if already present.
                $users[$email] = $user;
            }

            $doctors = [
                ['rana@taqweem.test', 'طب الأسنان العام', 'TQ-LOCAL-RANA', 7, 25],
                ['yazan@taqweem.test', 'تقويم الأسنان', 'TQ-LOCAL-YAZAN', 9, 40],
                ['hiba@taqweem.test', 'تجميل الأسنان', 'TQ-LOCAL-HIBA', 6, 35],
            ];
            $services = [];
            $profiles = [];
            foreach ($doctors as [$email, $specialityName, $license, $years, $fee]) {
                $speciality = Specialities::firstOrCreate(['name' => $specialityName], [
                    'description' => 'تخصص ضمن سجلات التدريب المحلية.',
                ]);
                $profile = $users[$email]->doctor_profile;
                if (!$profile) {
                    $profile = $users[$email]->doctor_profile()->create([
                        'speciality_id' => $speciality->id,
                        'license_number' => $license,
                        'bio' => 'ملف مخصص لاختبار تجربة الحجز في نظام تقويم.',
                        'years_experience' => $years,
                        'consultation_fee' => $fee,
                    ]);
                }
                $profiles[$email] = $profile;

                foreach ([['فحص الأسنان والاستشارة', 30, 25], ['جلسة متابعة', 45, 35]] as [$name, $minutes, $price]) {
                    $type = AppointmentTypes::firstOrCreate(['name' => $name], [
                        'description' => 'خدمة مسجلة لأغراض الاختبار المحلي.',
                    ]);
                    $services[$email][] = $profile->doctor_appointment_types()->firstOrCreate(
                        ['appointment_type_id' => $type->id],
                        ['duration_minutes' => $minutes, 'price' => $price]
                    );
                }
                for ($day = 0; $day <= 6; $day++) {
                    $exists = $profile->doctor_schedules()
                        ->where('day_of_week', $day)->where('start_time', '09:00:00')->exists();
                    if (! $exists) {
                        // DoctorSchedules intentionally has no mass-assignable columns.
                        $schedule = new DoctorSchedules();
                        $schedule->forceFill([
                            'day_of_week' => $day,
                            'start_time' => '09:00:00',
                            'end_time' => '17:00:00',
                            'is_available' => true,
                        ]);
                        $profile->doctor_schedules()->save($schedule);
                    }
                }
            }

            $specs = [
                ['ahmad@taqweem.test', 'rana@taqweem.test', 3, '10:00', 'pending', 'زيارة فحص دورية'],
                ['layan@taqweem.test', 'yazan@taqweem.test', 4, '11:00', 'confirmed', 'متابعة التقويم'],
                ['omar@taqweem.test', 'hiba@taqweem.test', 5, '12:00', 'cancelled', 'استشارة تجميل الأسنان'],
                ['ahmad@taqweem.test', 'yazan@taqweem.test', -7, '11:00', 'completed', 'متابعة جلسة سابقة'],
                ['ahmad@taqweem.test', 'yazan@taqweem.test', -1, '10:00', 'completed', Appointments::AI_REPORT_DEMO_REASON],
            ];
            foreach ($specs as [$patient, $doctor, $offset, $time, $status, $reason]) {
                $isReportDemo = $reason === Appointments::AI_REPORT_DEMO_REASON;
                $at = now()->startOfDay()->addDays($offset)->setTimeFromTimeString($time);
                $visit = Appointments::firstOrCreate([
                    'patient_id' => $users[$patient]->id,
                    'doctor_appointment_type_id' => $services[$doctor][0]->id,
                    'reason' => $isReportDemo ? $reason : 'LOCAL-TEST: ' . $reason,
                ], [
                    'scheduled_at' => $at,
                    'status' => $status,
                ]);

                if ($status === 'completed') {
                    $doctorProfile = $profiles[$doctor];
                    $note = MedicalNotes::where('appointment_id', $visit->id)
                        ->where('doctor_profile_id', $doctorProfile->id)->first();
                    if (!$note) {
                        $note = new MedicalNotes();
                        $note->forceFill([
                            'appointment_id' => $visit->id,
                            'doctor_profile_id' => $doctorProfile->id,
                            'diagnosis' => $isReportDemo
                                ? 'زيارة متابعة للتقويم الثابت. الحاصرات مستقرة ولا توجد حاصرات مفكوكة.'
                                : 'مراجعة متابعة روتينية (سجل تدريبي، ليس تشخيصاً طبياً حقيقياً).',
                            'notes' => $isReportDemo
                                ? 'فحص الطبيب السلك والحاصرات. أكد ضرورة تنظيف ما حول الحاصرات يومياً، والمتابعة بعد أربعة أسابيع.'
                                : 'مثال على ملاحظة يراجعها الطبيب بعد إتمام الموعد.',
                            'prescription' => null,
                        ])->save();
                    }
                    if (! $isReportDemo && ! $note->medical_report()->exists()) {
                        // status and approved_at are guarded on MedicalReport by design.
                        $report = new MedicalReport();
                        $report->forceFill([
                            'medical_note_id' => $note->id,
                            'content' => 'تقرير تدريبي لزيارة متابعة الأسنان. ليس تقريراً طبياً فعلياً ولا مخرجات AI.',
                            'status' => 'approved',
                            'approved_at' => now(),
                        ])->save();
                    }
                }
            }

            return [
                'accounts' => count($users),
                'doctors' => count($doctors),
                'appointments' => count($specs),
            ];
        }, 3);

        $this->command?->info('Local-only records prepared in taqweem. Existing real accounts untouched.');
        $this->command?->table(['Type', 'Count'], array_map(
            static fn($type, $count) => [$type, $count],
            array_keys($summary),
            $summary
        ));
        $this->command?->warn('All @taqweem.test accounts are training accounts only. Do NOT deploy them to AWS.');
    }
}
