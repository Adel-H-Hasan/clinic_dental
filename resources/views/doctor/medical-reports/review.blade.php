<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>مراجعة مسودّة التقرير | MedAssist Dental</title>
    <link rel="stylesheet" href="{{ asset('css/medical-reports.css') }}">
</head>
<body class="report-page report-doctor">
    @php($appointment = $report->medical_note->appointment)

    <header class="report-brand"><bdi>MedAssist <span>Dental</span></bdi></header>

    <main class="report-shell">
        <a class="report-back" href="{{ route('doctor.appointments.medical-notes.index', $appointment->id) }}">
            العودة إلى الملاحظات الطبية
        </a>

        <article class="report-card">
            <header class="report-card-heading">
                <div>
                    <h1>مراجعة مسودّة التقرير</h1>
                    <p>راجع النص الذي سيقرأه المريض وعدّله قبل النشر.</p>
                </div>
                <span class="report-status">مسودّة</span>
            </header>

            <dl class="report-details">
                <div>
                    <dt>رقم الموعد</dt>
                    <dd><bdi>{{ $appointment->id }}</bdi></dd>
                </div>
                <div>
                    <dt>تاريخ الزيارة</dt>
                    <dd><bdi>{{ $appointment->scheduled_at }}</bdi></dd>
                </div>
            </dl>

            <form method="POST" action="{{ route('doctor.medical-notes.report.approve', $report->medical_note_id) }}">
                @csrf

                <label class="report-label" for="report-content">نص التقرير للمريض</label>
                <textarea class="report-input" id="report-content" name="content" rows="9"
                          dir="auto" required>{{ old('content', $report->content) }}</textarea>

                @error('content')
                    <p class="report-error" role="alert">أدخل نص التقرير قبل اعتماده.</p>
                @enderror

                <div class="report-actions">
                    <button class="report-primary" type="submit">اعتماد ونشر التقرير</button>
                    <p>يظهر للمريض بعد الاعتماد.</p>
                </div>
            </form>
        </article>
    </main>
</body>
</html>
