<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تقرير زيارة الأسنان | MedAssist Dental</title>
    <link rel="stylesheet" href="{{ asset('css/medical-reports.css') }}">
</head>
<body class="report-page report-patient">
    @php($appointment = $report->medical_note->appointment)

    <main class="report-shell">
        <article class="report-card">
            <header class="report-card-heading">
                <div>
                    <h1>تقرير زيارة الأسنان</h1>
                    <p><bdi>MedAssist Dental</bdi></p>
                </div>
                <span class="report-status">معتمد</span>
            </header>

            <dl class="report-details">
                <div>
                    <dt>المريض</dt>
                    <dd>{{ auth()->user()->name }}</dd>
                </div>
                <div>
                    <dt>تاريخ الزيارة</dt>
                    <dd><bdi>{{ $appointment->scheduled_at }}</bdi></dd>
                </div>
            </dl>

            <section class="report-summary">
                <h2>ملخّص الزيارة والمتابعة</h2>
                <div class="report-text" dir="auto">{{ $report->content }}</div>
            </section>

            <footer class="report-footer">
                <span>رقم التقرير <bdi>{{ $report->id }}</bdi></span>
                <span>تاريخ الاعتماد <bdi>{{ $report->approved_at }}</bdi></span>
            </footer>
        </article>
    </main>
</body>
</html>
