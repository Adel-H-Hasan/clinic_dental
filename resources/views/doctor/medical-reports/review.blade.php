<x-workspace title="{{ __('مراجعة مسودّة التقرير') }}">
<div class="panel">
    @php($appointment = $report->medical_note->appointment)



    <div class="view-content">
        <a class="report-back" href="{{ route('doctor.appointments.medical-notes.index', $appointment->id) }}">
            {{ __('العودة إلى الملاحظات الطبية') }}
        </a>

        <article class="report-card">
            <header class="report-card-heading">
                <div>

                    <p>{{ __('راجع النص الذي سيقرأه المريض وعدّله قبل النشر.') }}</p>
                </div>
                <span class="report-status">{{ __('مسودّة') }}</span>
            </header>

            <dl class="report-details">
                <div>
                    <dt>{{ __('رقم الموعد') }}</dt>
                    <dd><bdi>{{ $appointment->id }}</bdi></dd>
                </div>
                <div>
                    <dt>{{ __('تاريخ الزيارة') }}</dt>
                    <dd><bdi>{{ $appointment->scheduled_at }}</bdi></dd>
                </div>
            </dl>

            <form method="POST" action="{{ route('doctor.medical-notes.report.approve', $report->medical_note_id) }}">
                @csrf

                <label class="report-label" for="report-content">{{ __('نص التقرير للمريض') }}</label>
                <textarea class="report-input" id="report-content" name="content" rows="9"
                          dir="auto" required>{{ old('content', $report->content) }}</textarea>

                <div class="report-actions">
                    <button class="report-primary" type="submit">{{ __('اعتماد ونشر التقرير') }}</button>
                    <p>{{ __('يظهر للمريض بعد الاعتماد.') }}</p>
                </div>
            </form>
        </article>
    </div>

</div>
</x-workspace>
