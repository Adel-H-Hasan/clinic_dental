# مراجعة تغييرات الخلفية — لم تُعتمد بعد

التاريخ: 21 نوفمبر 2026. هذه التغييرات موجودة من التقدم السابق، ولم تُطبّق تعديلات إضافية عليها أثناء استكمال الواجهة. الاختبارات الحالية تشغّل الشجرة التي تتضمنها؛ نجاح الاختبارات لا يعني موافقتك عليها. لم تُجرَ أي تغييرات على النماذج أو العلاقات أو السياسات أو الوسائط أو الترحيلات أو خدمات الذكاء الاصطناعي.

المقارنة مع الالتزام الأساسي `8c285b9d0e2fbc42571eb54bcb4c0193287629dc`.

## app/Http/Controllers/AuthController.php

الدوال: login / logout.

السبب والأثر: يغيّر المسار الافتراضي للمريض بعد الدخول إلى لوحته، ويحفظ لغة الجلسة عند الخروج. لا يغيّر فحص كلمة السر أو التحقق من البريد أو صلاحيات الأدوار.

المخاطر: ما زال redirect()->intended() الأصلي مقدّماً على المسار الافتراضي؛ إذا زار الضيف رابط مريض ثم دخل بحساب مدير فقد يتجه إلى الرابط الممنوع ويحصل على 403. لم يُصلح هذا السلوك لأنه يتطلب موافقة منفصلة.

التحقق: اختبارات الرحلة الحقيقية باللغتين، واختبارات التكامل الموضحة في تقرير التسليم.

```diff
diff --git a/app/Http/Controllers/AuthController.php b/app/Http/Controllers/AuthController.php
index a8fe35c..2bef3cc 100644
--- a/app/Http/Controllers/AuthController.php
+++ b/app/Http/Controllers/AuthController.php
@@ -43,7 +43,7 @@ public function login(Request $request)
         $request->session()->regenerate();
 
         $fallback = match ($request->user()->role) {
-            'patient' => route('patient.profile.show'),
+            'patient' => route('patient.dashboard'),
             'doctor' => route('doctor.dashboard'),
             'admin' => route('admin.dashboard'),
         };
@@ -157,9 +157,11 @@ public function resetPassword(Request $request)
 
     public function logout(Request $request)
     {
+        $locale = app()->getLocale();
         Auth::logout();
         $request->session()->invalidate();
         $request->session()->regenerate();
+        $request->session()->put('locale', $locale);
 
         return redirect('/');
     }

```

## app/Http/Controllers/DoctorProfileController.php

الدوال: edit.

السبب والأثر: يجلب ملف الطبيب من علاقة المستخدم الحالي عبر الاستعلام، بدلاً من استدعاء firstOrFail على النموذج.

المخاطر: يمس تحديد الملف المعروض للتحرير؛ اختُبر بحساب طبيب ثانٍ للتأكد من عدم عرض ملف الطبيب الأول. لا تغيير في العلاقات أو عمليات الحفظ.

التحقق: اختبارات الرحلة الحقيقية باللغتين، واختبارات التكامل الموضحة في تقرير التسليم.

```diff
diff --git a/app/Http/Controllers/DoctorProfileController.php b/app/Http/Controllers/DoctorProfileController.php
index 80107e1..ec6a3ed 100644
--- a/app/Http/Controllers/DoctorProfileController.php
+++ b/app/Http/Controllers/DoctorProfileController.php
@@ -26,7 +26,7 @@ public function show(Request $request)
      */
     public function edit(Request $request)
     {
-        $profile = $request->user()->doctor_profile->firstOrFail();
+        $profile = $request->user()->doctor_profile()->firstOrFail();
 
         return view('doctor.profile.edit', compact('profile'));
     }

```

## routes/web.php

الدوال: مسار /، مجموعة patient، مسارات workspace وlanguage.switch.

السبب والأثر: يربط الرئيسية بالنسخة السينمائية، ويضيف شاشات عرض للبيانات تحت حراس الأدوار الحاليين، ويعيد المستخدم إلى صفحته بعد تغيير اللغة.

المخاطر: يضيف أسطح عرض جديدة ويغير الصفحة الرئيسية وسلوك رجوع اللغة. صفحة نجاح الحجز تعتمد على رسالة النجاح الأصلية، وأي تغيير لاحق في نصها يستلزم مراجعة. مسار GET لمراجعة الحجز له اسم موروث patient.؛ لم يُغيّر دون موافقة. عمليات الحجز تبقى لدى المتحكم الأصلي.

التحقق: اختبارات الرحلة الحقيقية باللغتين، واختبارات التكامل الموضحة في تقرير التسليم.

```diff
diff --git a/routes/web.php b/routes/web.php
index 12fbe97..e94a360 100644
--- a/routes/web.php
+++ b/routes/web.php
@@ -22,9 +22,31 @@
 // Number of middlewares used => 7
 
 Route::get('/', function () {
-    return view('welcome');
+    return view('taqweem-landing');
+})->name('landing');
+
+Route::get('/dentists', [\App\Http\Controllers\WorkspaceController::class, 'directory'])->name('dentists.index');
+Route::get('/dentists/{id}', [\App\Http\Controllers\WorkspaceController::class, 'dentist'])->whereNumber('id')->name('dentists.show');
+Route::middleware(['auth', 'patientOnly', 'verified'])->prefix('patient')->name('patient.')->group(function () {
+    Route::get('/dashboard', [\App\Http\Controllers\WorkspaceController::class, 'dashboard'])->name('dashboard');
+    Route::get('/booking', [\App\Http\Controllers\WorkspaceController::class, 'booking'])->name('booking');
+    Route::post('/booking/review', [\App\Http\Controllers\WorkspaceController::class, 'reviewBooking'])->name('booking.review');
+    Route::get('/booking/review', function () {
+        if (session('success') === 'Appointment booked successfully.') return view('patient.booking-success');
+        session()->reflash();
+        return redirect()->route('patient.booking');
+    });
+    Route::get('/visits/{id}', [\App\Http\Controllers\WorkspaceController::class, 'detail'])->whereNumber('id')->name('visits.show');
 });
 
+// Additive HTML screens: retain the original appointment JSON contracts.
+Route::get('/patient/workspace', [\App\Http\Controllers\WorkspaceController::class, 'patient'])
+    ->middleware(['auth', 'patientOnly', 'verified'])->name('patient.workspace');
+Route::get('/doctor/workspace', [\App\Http\Controllers\WorkspaceController::class, 'doctor'])
+    ->middleware(['auth', 'doctorOnly'])->name('doctor.workspace');
+Route::get('/admin/service-catalog', [\App\Http\Controllers\WorkspaceController::class, 'types'])
+    ->middleware(['auth', 'adminOnly'])->name('admin.types.workspace');
+
 // Admin doctors CRUD routes
 Route::middleware(['auth', 'adminOnly'])
     ->prefix('admin')
@@ -230,7 +252,12 @@
 
     session(['locale' => $locale]);
 
-    return redirect()->route('home');
+    $return = request()->query('return', '/');
+    if (!is_string($return) || !str_starts_with($return, '/') || str_starts_with($return, '//') || str_contains($return, '\\') || preg_match('/[\x00-\x20]/', $return)) {
+        $return = '/';
+    }
+    session()->reflash();
+    return redirect($return);
 })->name('language.switch');
 
 // Smile AI routes

```

## app/Http/Controllers/WorkspaceController.php

الدوال: dashboard / directory / dentist / booking / reviewBooking / detail / patient / doctor / types.

السبب والأثر: متحكم عرض جديد للقراءة: بيانات اللوحات ودليل الأطباء والمواعيد والتقارير المعتمدة وأنواع المواعيد.

المخاطر: يعرض أوقاتاً مقترحة كل 15 دقيقة من الجداول والحجوزات؛ ليس بديلاً عن التحقق النهائي والمعاملة في متحكم الحجز الأصلي. يجب اعتماد افتراض العرض هذا. يقيّد الزيارات والتقارير بمعرّف المريض والطبيب؛ اختُبرت محاولات الوصول المتقاطع.

التحقق: اختبارات الرحلة الحقيقية باللغتين، واختبارات التكامل الموضحة في تقرير التسليم.

```diff
diff --git a/app/Http/Controllers/WorkspaceController.php b/app/Http/Controllers/WorkspaceController.php
new file mode 100644
index 0000000..bdc4573
--- /dev/null
+++ b/app/Http/Controllers/WorkspaceController.php
@@ -0,0 +1,116 @@
+<?php
+
+namespace App\Http\Controllers;
+
+use App\Models\Appointments;
+use App\Models\AppointmentTypes;
+use App\Models\DoctorAppointmentTypes;
+use App\Models\MedicalReport;
+use App\Models\DoctorProfile;
+use App\Models\Specialities;
+use Carbon\Carbon;
+use Illuminate\Http\Request;
+
+/** Read-only presentation endpoints; all mutations use existing controllers. */
+class WorkspaceController extends Controller
+{
+    public function dashboard(Request $request)
+    {
+        $visits = Appointments::where('patient_id', $request->user()->id);
+        $upcomingCount = (clone $visits)->whereIn('status', ['pending', 'confirmed'])->where('scheduled_at', '>', now())->count();
+        $completedCount = (clone $visits)->where('status', 'completed')->count();
+        $reportsCount = MedicalReport::where('status', 'approved')->whereRelation('medical_note.appointment', 'patient_id', $request->user()->id)->count();
+        $next = (clone $visits)->with('doctor_appointment_type.appointment_type', 'doctor_appointment_type.doctor_profile.user')
+            ->whereIn('status', ['pending', 'confirmed'])->where('scheduled_at', '>', now())->orderBy('scheduled_at')->first();
+        return response()->view('patient.dashboard', compact('upcomingCount', 'completedCount', 'reportsCount', 'next'))->header('Cache-Control', 'private, no-store');
+    }
+
+    public function directory(Request $request)
+    {
+        $filters = $request->validate(['q' => 'nullable|string|max:100', 'speciality' => 'nullable|integer']);
+        $doctors = DoctorProfile::with('user', 'speciality')->where('is_active', true)
+            ->whereHas('user', fn ($q) => $q->where('is_active', true)->when($filters['q'] ?? null, fn ($q, $name) => $q->where('name', 'like', '%'.$name.'%')))
+            ->when($filters['speciality'] ?? null, fn ($q, $id) => $q->where('speciality_id', $id))
+            ->paginate(12)->withQueryString();
+        return view('public.dentists', ['doctors' => $doctors, 'specialities' => Specialities::orderBy('name')->get()]);
+    }
+
+    public function dentist(int $id)
+    {
+        $doctor = DoctorProfile::with('user', 'speciality', 'doctor_appointment_types.appointment_type', 'doctor_schedules')
+            ->where('is_active', true)->whereHas('user', fn ($q) => $q->where('is_active', true))->findOrFail($id);
+        return view('public.dentist', compact('doctor'));
+    }
+
+    public function booking(Request $request)
+    {
+        $data = $request->validate(['service' => 'nullable|integer|exists:doctor_appointment_types,id', 'date' => 'nullable|date_format:Y-m-d|after_or_equal:today']);
+        $services = DoctorAppointmentTypes::with('appointment_type', 'doctor_profile.user')
+            ->whereHas('doctor_profile', fn ($q) => $q->where('is_active', true)->whereHas('user', fn ($q) => $q->where('is_active', true)))
+            ->where('duration_minutes', '>', 0)->get();
+        $service = $services->firstWhere('id', $data['service'] ?? null);
+        $date = $data['date'] ?? now()->toDateString();
+        $slots = [];
+        if ($service) {
+            $day = Carbon::parse($date);
+            $schedules = $service->doctor_profile->doctor_schedules()->where('is_available', true)->where('day_of_week', $day->dayOfWeek)->get();
+            $bookings = Appointments::with('doctor_appointment_type')->whereRelation('doctor_appointment_type', 'doctor_profile_id', $service->doctor_profile_id)
+                ->whereIn('status', ['pending', 'confirmed'])->whereDate('scheduled_at', $date)->get();
+            foreach ($schedules as $schedule) {
+                $start = Carbon::parse($date.' '.$schedule->start_time);
+                $close = Carbon::parse($date.' '.$schedule->end_time);
+                for ($time = $start->copy(); $time->copy()->addMinutes($service->duration_minutes)->lte($close); $time->addMinutes(15)) {
+                    $end = $time->copy()->addMinutes($service->duration_minutes);
+                    if (!$time->isFuture() || !$time->isSameDay($end)) continue;
+                    $overlap = $bookings->contains(fn ($visit) => $visit->scheduled_at->lt($end) && $visit->scheduled_at->copy()->addMinutes($visit->doctor_appointment_type->duration_minutes)->gt($time));
+                    if (!$overlap) $slots[$time->format('Y-m-d\TH:i')] = $time->format('H:i');
+                }
+            }
+            ksort($slots);
+        }
+        return view('patient.booking', compact('services', 'service', 'date', 'slots'));
+    }
+
+    public function reviewBooking(Request $request)
+    {
+        $data = $request->validate(['doctor_appointment_type_id' => 'required|integer|exists:doctor_appointment_types,id', 'scheduled_at' => 'required|date|after:now', 'reason' => 'nullable|string|max:1000']);
+        $service = DoctorAppointmentTypes::with('doctor_profile.user', 'appointment_type')->findOrFail($data['doctor_appointment_type_id']);
+        return view('patient.booking-review', compact('data', 'service'));
+    }
+
+    public function detail(Request $request, int $id)
+    {
+        $appointment = Appointments::with('doctor_appointment_type.doctor_profile.user', 'doctor_appointment_type.appointment_type')
+            ->where('patient_id', $request->user()->id)->findOrFail($id);
+        return response()->view('patient.appointment', compact('appointment'))->header('Cache-Control', 'private, no-store');
+    }
+    public function patient(Request $request)
+    {
+        $appointments = Appointments::with('doctor_appointment_type.appointment_type', 'doctor_appointment_type.doctor_profile.user')
+            ->where('patient_id', $request->user()->id)->orderByDesc('scheduled_at')->paginate(15);
+        $services = DoctorAppointmentTypes::with('appointment_type', 'doctor_profile.user', 'doctor_profile.doctor_schedules')
+            ->whereHas('doctor_profile', fn ($query) => $query->where('is_active', true)->whereHas('user', fn ($q) => $q->where('is_active', true)))->get();
+        $reports = MedicalReport::where('status', 'approved')
+            ->whereRelation('medical_note.appointment', 'patient_id', $request->user()->id)->latest()->get();
+
+        return response()->view('patient.workspace', compact('appointments', 'services', 'reports'))
+            ->header('Cache-Control', 'private, no-store');
+    }
+
+    public function doctor(Request $request)
+    {
+        $doctor = $request->user()->doctor_profile;
+        abort_unless($doctor, 403);
+        $appointments = Appointments::with('user', 'doctor_appointment_type.appointment_type')
+            ->whereRelation('doctor_appointment_type', 'doctor_profile_id', $doctor->id)
+            ->orderByDesc('scheduled_at')->paginate(20);
+
+        return response()->view('doctor.appointments', compact('appointments'))
+            ->header('Cache-Control', 'private, no-store');
+    }
+
+    public function types()
+    {
+        return view('admin.appointment-types', ['types' => AppointmentTypes::orderBy('name')->paginate(20)]);
+    }
+}

```

## قرار مطلوب

تحتاج الملفات الأربعة أعلاه إلى موافقتك الصريحة قبل اعتمادها أو تعديلها. لا يوجد دمج أو التزام نهائي، ولا تراجع تلقائي قد يمحو عمل الفريق. مشكلة intended أعلاه موثّقة فقط ولم يُطبّق إصلاح لها.
