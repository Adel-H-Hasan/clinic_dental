# جرد المسارات والشاشات

جرد آلي من Laravel الحالي، وليس استنتاجاً من صور التصميم. المسارات الإضافية ما زالت ضمن طلب اعتماد الخلفية. عمود القالب يبيّن استدعاءات view الثابتة التي يمكن استخراجها؛ غيابه لا يعني غياب واجهة. الرئيسية تعرض taqweem-landing، وبعض المسارات تنفيذ أو إعادة توجيه فقط.

| الطريقة | المسار | الاسم | التنفيذ | القوالب | الحماية |
|---|---|---|---|---|---|
| GET / HEAD | /up | — | Closure | — | — |
| GET / HEAD | / | landing | Closure | — | web |
| GET / HEAD | /dentists | dentists.index | App\Http\Controllers\WorkspaceController@directory | public.dentists | web |
| GET / HEAD | /dentists/{id} | dentists.show | App\Http\Controllers\WorkspaceController@dentist | public.dentist | web |
| GET / HEAD | /patient/dashboard | patient.dashboard | App\Http\Controllers\WorkspaceController@dashboard | patient.dashboard | web, auth, patientOnly, verified |
| GET / HEAD | /patient/booking | patient.booking | App\Http\Controllers\WorkspaceController@booking | patient.booking | web, auth, patientOnly, verified |
| POST | /patient/booking/review | patient.booking.review | App\Http\Controllers\WorkspaceController@reviewBooking | patient.booking-review | web, auth, patientOnly, verified |
| GET / HEAD | /patient/booking/review | patient. | Closure | — | web, auth, patientOnly, verified |
| GET / HEAD | /patient/visits/{id} | patient.visits.show | App\Http\Controllers\WorkspaceController@detail | patient.appointment | web, auth, patientOnly, verified |
| GET / HEAD | /patient/workspace | patient.workspace | App\Http\Controllers\WorkspaceController@patient | patient.workspace | web, auth, patientOnly, verified |
| GET / HEAD | /doctor/workspace | doctor.workspace | App\Http\Controllers\WorkspaceController@doctor | doctor.appointments | web, auth, doctorOnly |
| GET / HEAD | /admin/service-catalog | admin.types.workspace | App\Http\Controllers\WorkspaceController@types | admin.appointment-types | web, auth, adminOnly |
| GET / HEAD | /admin/dashboard | admin.dashboard | App\Http\Controllers\Admin\DashboardController@index | admin.dashboard | web, auth, adminOnly |
| GET / HEAD | /admin/specialities | admin.specialities.index | App\Http\Controllers\Admin\SpecialitiesController@index | — | web, auth, adminOnly |
| GET / HEAD | /admin/specialities/create | admin.specialities.create | App\Http\Controllers\Admin\SpecialitiesController@create | admin.specialities.create | web, auth, adminOnly |
| POST | /admin/specialities | admin.specialities.store | App\Http\Controllers\Admin\SpecialitiesController@store | — | web, auth, adminOnly |
| GET / HEAD | /admin/specialities/{id} | admin.specialities.edit | App\Http\Controllers\Admin\SpecialitiesController@edit | admin.specialities.edit | web, auth, adminOnly |
| PUT | /admin/specialities/{id} | admin.specialities.update | App\Http\Controllers\Admin\SpecialitiesController@update | — | web, auth, adminOnly |
| DELETE | /admin/specialities/{id} | admin.specialities.destroy | App\Http\Controllers\Admin\SpecialitiesController@destroy | — | web, auth, adminOnly |
| GET / HEAD | /admin/doctors | admin.doctors.index | App\Http\Controllers\Admin\DoctorController@index | admin.doctors.index | web, auth, adminOnly |
| GET / HEAD | /admin/doctors/create | admin.doctors.create | App\Http\Controllers\Admin\DoctorController@create | admin.doctors.create | web, auth, adminOnly |
| POST | /admin/doctors | admin.doctors.store | App\Http\Controllers\Admin\DoctorController@store | — | web, auth, adminOnly |
| GET / HEAD | /admin/doctors/{doctor} | admin.doctors.show | App\Http\Controllers\Admin\DoctorController@show | admin.doctors.show | web, auth, adminOnly |
| GET / HEAD | /admin/doctors/{doctor}/edit | admin.doctors.edit | App\Http\Controllers\Admin\DoctorController@edit | admin.doctors.edit | web, auth, adminOnly |
| PUT / PATCH | /admin/doctors/{doctor} | admin.doctors.update | App\Http\Controllers\Admin\DoctorController@update | — | web, auth, adminOnly |
| DELETE | /admin/doctors/{doctor} | admin.doctors.destroy | App\Http\Controllers\Admin\DoctorController@destroy | — | web, auth, adminOnly |
| GET / HEAD | /admin/users | admin.users.index | App\Http\Controllers\UserController@index | admin.users.index | web, auth, adminOnly |
| GET / HEAD | /admin/users/{user} | admin.users.show | App\Http\Controllers\UserController@show | admin.users.show | web, auth, adminOnly |
| GET / HEAD | /admin/users/{user}/edit | admin.users.edit | App\Http\Controllers\UserController@edit | admin.users.edit | web, auth, adminOnly |
| PUT / PATCH | /admin/users/{user} | admin.users.update | App\Http\Controllers\UserController@update | — | web, auth, adminOnly |
| GET / HEAD | /admin/appointment-types | admin.appointment-types.index | App\Http\Controllers\AppointmentTypesController@index | — | web, auth, adminOnly |
| POST | /admin/appointment-types | admin.appointment-types.store | App\Http\Controllers\AppointmentTypesController@store | — | web, auth, adminOnly |
| PUT / PATCH | /admin/appointment-types/{appointment_type} | admin.appointment-types.update | App\Http\Controllers\AppointmentTypesController@update | — | web, auth, adminOnly |
| DELETE | /admin/appointment-types/{appointment_type} | admin.appointment-types.destroy | App\Http\Controllers\AppointmentTypesController@destroy | — | web, auth, adminOnly |
| GET / HEAD | /doctor/dashboard | doctor.dashboard | App\Http\Controllers\DoctorDashboardController@index | doctor.dashboard | web, auth, doctorOnly |
| GET / HEAD | /doctor/profile | doctor.profile.show | App\Http\Controllers\DoctorProfileController@show | doctor.profile.show | web, auth, doctorOnly |
| GET / HEAD | /doctor/profile/edit | doctor.profile.edit | App\Http\Controllers\DoctorProfileController@edit | doctor.profile.edit | web, auth, doctorOnly |
| PUT / PATCH | /doctor/profile | doctor.profile.update | App\Http\Controllers\DoctorProfileController@update | — | web, auth, doctorOnly |
| GET / HEAD | /doctor/schedules | doctor.schedules.index | App\Http\Controllers\DoctorSchedulesController@index | doctor.schedules.index | web, auth, doctorOnly |
| POST | /doctor/schedules | doctor.schedules.store | App\Http\Controllers\DoctorSchedulesController@store | — | web, auth, doctorOnly |
| GET / HEAD | /doctor/schedules/{id}/edit | doctor.schedules.edit | App\Http\Controllers\DoctorSchedulesController@edit | doctor.schedules.edit | web, auth, doctorOnly |
| PUT | /doctor/schedules/{id} | doctor.schedules.update | App\Http\Controllers\DoctorSchedulesController@update | — | web, auth, doctorOnly |
| DELETE | /doctor/schedules/{id} | doctor.schedules.destroy | App\Http\Controllers\DoctorSchedulesController@destroy | — | web, auth, doctorOnly |
| GET / HEAD | /doctor/services | doctor.services.index | App\Http\Controllers\DoctorAppointmentTypesController@index | doctor.services.index | web, auth, doctorOnly |
| POST | /doctor/services | doctor.services.store | App\Http\Controllers\DoctorAppointmentTypesController@store | — | web, auth, doctorOnly |
| GET / HEAD | /doctor/services/{id}/edit | doctor.services.edit | App\Http\Controllers\DoctorAppointmentTypesController@edit | doctor.services.edit | web, auth, doctorOnly |
| PUT | /doctor/services/{id} | doctor.services.update | App\Http\Controllers\DoctorAppointmentTypesController@update | — | web, auth, doctorOnly |
| DELETE | /doctor/services/{id} | doctor.services.destroy | App\Http\Controllers\DoctorAppointmentTypesController@destroy | — | web, auth, doctorOnly |
| GET / HEAD | /doctor/appointments/{id}/medical-notes | doctor.appointments.medical-notes.index | App\Http\Controllers\MedicalNotesController@index | doctor.medical-notes.index | web, auth, doctorOnly |
| POST | /doctor/appointments/{id}/medical-notes | doctor.appointments.medical-notes.store | App\Http\Controllers\MedicalNotesController@store | — | web, auth, doctorOnly |
| GET / HEAD | /doctor/appointments/{id}/medical-notes/{noteId}/edit | doctor.appointments.medical-notes.edit | App\Http\Controllers\MedicalNotesController@edit | doctor.medical-notes.edit | web, auth, doctorOnly |
| PUT | /doctor/appointments/{id}/medical-notes/{noteId} | doctor.appointments.medical-notes.update | App\Http\Controllers\MedicalNotesController@update | — | web, auth, doctorOnly |
| POST | /doctor/appointments/{id}/medical-notes/{noteId}/review | doctor.appointments.medical-notes.review | App\Http\Controllers\MedicalNotesController@review | — | web, auth, doctorOnly |
| POST | /doctor/medical-notes/{noteId}/report | doctor.medical-notes.report.store | App\Http\Controllers\MedicalReportsController@store | — | web, auth, doctorOnly |
| GET / HEAD | /doctor/medical-notes/{noteId}/report/review | doctor.medical-notes.report.review | App\Http\Controllers\MedicalReportsController@review | doctor.medical-reports.review | web, auth, doctorOnly |
| POST | /doctor/medical-notes/{noteId}/report/approve | doctor.medical-notes.report.approve | App\Http\Controllers\MedicalReportsController@approve | — | web, auth, doctorOnly |
| PATCH | /doctor/appointments/{id}/status | doctor.appointments.status | App\Http\Controllers\AppointmentsController@updateStatus | — | web, auth, doctorOnly |
| GET / HEAD | /doctor/appointments | doctor.appointments.index | App\Http\Controllers\AppointmentsController@doctorIndex | — | web, auth, doctorOnly |
| GET / HEAD | /patient/profile | patient.profile.show | App\Http\Controllers\PatientProfileController@show | patient.profile.show | web, auth, patientOnly, verified |
| GET / HEAD | /patient/profile/edit | patient.profile.edit | App\Http\Controllers\PatientProfileController@edit | patient.profile.edit | web, auth, patientOnly, verified |
| PUT / PATCH | /patient/profile | patient.profile.update | App\Http\Controllers\PatientProfileController@update | — | web, auth, patientOnly, verified |
| GET / HEAD | /patient/reports/{reportId} | patient.reports.show | App\Http\Controllers\MedicalReportsController@show | patient.medical-reports.show | web, auth, patientOnly, verified |
| GET / HEAD | /patient/appointments | patient.appointments.index | App\Http\Controllers\AppointmentsController@index | — | web, auth, patientOnly, verified |
| POST | /patient/appointments | patient.appointments.store | App\Http\Controllers\AppointmentsController@store | — | web, auth, patientOnly, verified |
| PATCH | /patient/appointments/{id}/cancel | patient.appointments.cancel | App\Http\Controllers\AppointmentsController@cancel | — | web, auth, patientOnly, verified |
| PATCH | /patient/appointments/{id}/reschedule | patient.appointments.reschedule | App\Http\Controllers\AppointmentsController@reschedule | — | web, auth, patientOnly, verified |
| GET / HEAD | /patient/appointments/{id} | patient.appointments.show | App\Http\Controllers\AppointmentsController@show | — | web, auth, patientOnly, verified |
| GET / POST / HEAD | /login | login | App\Http\Controllers\AuthController@login | auth.login | web, guest |
| GET / POST / HEAD | /register | register | App\Http\Controllers\AuthController@register | auth.register | web, guest |
| GET / HEAD | /forgot-password | password.request | \Illuminate\Routing\ViewController | — | web, guest |
| POST | /forgot-password | password.email | App\Http\Controllers\AuthController@sendResetLink | — | web, guest |
| GET / HEAD | /reset-password/{token} | password.reset | App\Http\Controllers\AuthController@showResetPasswordForm | auth.reset-password | web, guest |
| POST | /reset-password | password.update | App\Http\Controllers\AuthController@resetPassword | — | web, guest |
| GET / HEAD | /email/verify | verification.notice | \Illuminate\Routing\ViewController | — | web, guest |
| GET / HEAD | /email/verify/{id}/{hash} | verification.verify | Closure | — | web, signed |
| POST | /logout | logout.submit | App\Http\Controllers\AuthController@logout | — | web, auth |
| POST | /smile-simulation/upload | smile.upload | App\Http\Controllers\AiImageAnalysesController@store | — | web, auth |
| GET / HEAD | /smile-simulation | smile.create | Closure | — | web |
| GET / HEAD | /home | home | Closure | — | web |
| GET / HEAD | /contact | contact | \Illuminate\Routing\ViewController | — | web |
| GET / HEAD | /language/{locale} | language.switch | Closure | — | web |
| GET / HEAD | /smile-ai | smile.ai.create | App\Http\Controllers\SmileAiController@create | smile-simulation.ai-create | web |
| POST | /smile-ai/generate | smile.ai.generate | App\Http\Controllers\SmileAiController@generate | — | web, throttle:3,1 |
| GET / HEAD | /smile-ai/result/{id} | smile.ai.show | App\Http\Controllers\SmileAiController@show | smile-simulation.ai-result | web |
| GET / HEAD | /smile-ai/result/{id}/image/{side} | smile.ai.image | App\Http\Controllers\SmileAiController@image | — | web |
| GET / HEAD | /smile-ai/result/{id}/download | smile.ai.download | App\Http\Controllers\SmileAiController@download | — | web |
| GET / HEAD | /smile-ai/gallery | smile.ai.gallery | App\Http\Controllers\SmileAiController@gallery | smile-simulation.ai-gallery | web |
| GET / HEAD | /storage/{path} | storage.local | Closure | — | — |
| PUT | /storage/{path} | storage.local.upload | Closure | — | — |
