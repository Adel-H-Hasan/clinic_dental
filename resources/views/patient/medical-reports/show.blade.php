<x-workspace title="{{ __('تقرير زيارة الأسنان') }}">
<div class="panel">
    @php($appointment = $report->medical_note->appointment)

    <div class="view-content">
        <article class="report-card">
            <header class="report-card-heading">
                <div>

                    <p><bdi>{{ __('MedAssist Dental') }}</bdi></p>
                </div>
                <span class="report-status">{{ __('معتمد') }}</span>
            </header>

            <dl class="report-details">
                <div>
                    <dt>{{ __('المريض') }}</dt>
                    <dd>{{ auth()->user()->name }}</dd>
                </div>
                <div>
                    <dt>{{ __('تاريخ الزيارة') }}</dt>
                    <dd><bdi>{{ $appointment->scheduled_at }}</bdi></dd>
                </div>
            </dl>

            <section class="report-summary">
                <h2>{{ __('ملخّص الزيارة والمتابعة') }}</h2>
                <div class="report-text" dir="auto">{{ $report->content }}</div>
            </section>

            <footer class="report-footer">
                <span>{{ __('رقم التقرير') }} <bdi>{{ $report->id }}</bdi></span>
                <span>{{ __('تاريخ الاعتماد') }} <bdi>{{ $report->approved_at }}</bdi></span>
            </footer>
        </article>
    </div>

</div>
</x-workspace>
