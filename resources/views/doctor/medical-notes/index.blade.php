<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Medical notes | MedAssist Dental</title>
    <link rel="stylesheet" href="{{ asset('css/medical-reports.css') }}">
</head>
<body class="report-page medical-notes">
    <header class="report-brand">MedAssist <span>Dental</span></header>

    <main class="report-shell">
        <section class="report-card">
            <header class="report-card-heading">
                <div>
                    <h1>Medical notes</h1>
                    <p>Appointment #{{ $appointment->id }}</p>
                </div>
                <span class="report-status {{ $appointment->status === 'completed' ? 'note-status-complete' : '' }}">
                    {{ ucfirst($appointment->status) }}
                </span>
            </header>

            @if ($appointment->status === 'completed')
                <form method="POST" class="note-form"
                      action="{{ route('doctor.appointments.medical-notes.store', $appointment->id) }}">
                    @csrf

                    <div class="note-fields">
                        <div class="note-field">
                            <label class="report-label" for="diagnosis">Diagnosis</label>
                            <textarea id="diagnosis" name="diagnosis" rows="3" dir="auto" required>{{ old('diagnosis') }}</textarea>
                        </div>

                        <div class="note-field">
                            <label class="report-label" for="prescription">Prescription</label>
                            <textarea id="prescription" name="prescription" rows="3" dir="auto">{{ old('prescription') }}</textarea>
                        </div>

                        <div class="note-field note-field-wide">
                            <label class="report-label" for="notes">Notes</label>
                            <textarea id="notes" name="notes" rows="3" dir="auto">{{ old('notes') }}</textarea>
                        </div>

                        <div class="note-field">
                            <label class="report-label" for="next_visit">Next visit</label>
                            <input id="next_visit" name="next_visit" type="datetime-local" value="{{ old('next_visit') }}">
                        </div>
                    </div>

                    @foreach ($errors->all() as $error)
                        <p class="report-error" role="alert">{{ $error }}</p>
                    @endforeach

                    <div class="report-actions">
                        <button type="submit" class="report-primary">Save medical note</button>
                    </div>
                </form>
            @else
                <p>You can add a note after the visit is completed.</p>
            @endif
        </section>

        <section class="note-list">
            <h2>Saved notes</h2>

            @forelse ($medicalNotes as $note)
                <article class="report-card">
                    <dl class="report-details">
                        <div class="note-field-wide">
                            <dt>Diagnosis</dt>
                            <dd class="report-text" dir="auto">{{ $note->diagnosis }}</dd>
                        </div>

                        @if ($note->prescription)
                            <div>
                                <dt>Prescription</dt>
                                <dd class="report-text" dir="auto">{{ $note->prescription }}</dd>
                            </div>
                        @endif

                        @if ($note->notes)
                            <div>
                                <dt>Notes</dt>
                                <dd class="report-text" dir="auto">{{ $note->notes }}</dd>
                            </div>
                        @endif

                        @if ($note->next_visit)
                            <div>
                                <dt>Next visit</dt>
                                <dd>{{ $note->next_visit }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($appointment->status === 'completed')
                        <div class="report-actions">
                            <a class="note-secondary"
                               href="{{ route('doctor.appointments.medical-notes.edit', [$appointment->id, $note->id]) }}">
                                Edit note
                            </a>

                            @if (config('services.gemini.demo_review'))
                                <form method="POST"
                                      action="{{ route('doctor.appointments.medical-notes.review', [$appointment->id, $note->id]) }}">
                                    @csrf
                                    <button type="submit" class="note-secondary">Review note</button>
                                </form>
                            @endif
                        </div>
                    @endif

                    @php
                        $review = session('medical_note_review');
                    @endphp

                    @if ($review && $review['note_id'] == $note->id)
                        @if ($review['suggestions'] === null)
                            <p class="report-error" role="alert">Gemini review is unavailable. Please try again.</p>
                        @elseif (empty($review['suggestions']))
                            <p>No missing information or conflicts were found.</p>
                        @else
                            <section class="note-section">
                                <h3>Suggestions for the doctor</h3>

                                @foreach ($review['suggestions'] as $suggestion)
                                    <div class="note-inset" dir="auto">
                                        <p>{{ $suggestion['question'] }}</p>

                                        @foreach ($suggestion['evidence'] as $quote)
                                            <blockquote>{{ $quote }}</blockquote>
                                        @endforeach
                                    </div>
                                @endforeach
                            </section>
                        @endif
                    @endif

                    @if ($note->medical_report)
                        <section class="note-section">
                            <header class="report-card-heading">
                                <h3>Medical report</h3>
                                <span class="report-status {{ $note->medical_report->status === 'approved' ? 'note-status-complete' : '' }}">
                                    {{ ucfirst($note->medical_report->status) }}
                                </span>
                            </header>
                            <div class="note-inset report-text" dir="auto">{{ $note->medical_report->content }}</div>

                            @if ($note->medical_report->status === 'draft' && $appointment->status === 'completed')
                                <div class="report-actions">
                                    <a class="note-secondary"
                                       href="{{ route('doctor.medical-notes.report.review', $note->id) }}">Review draft</a>
                                </div>
                            @endif
                        </section>
                    @elseif (config('services.gemini.demo_review') && $appointment->status === 'completed')
                        <form method="POST" class="note-section"
                              action="{{ route('doctor.medical-notes.report.store', $note->id) }}">
                            @csrf

                            <div class="note-field">
                                <label class="report-label" for="doctor-additions-{{ $note->id }}">Doctor-confirmed details</label>
                                <textarea id="doctor-additions-{{ $note->id }}" name="doctor_additions" rows="2" dir="auto"
                                          maxlength="2000">{{ session('report_error_note_id') == $note->id ? old('doctor_additions') : '' }}</textarea>
                            </div>

                            @if (session('report_error_note_id') == $note->id)
                                <p class="report-error" role="alert">Gemini could not generate a draft. Please try again.</p>
                            @endif

                            <div class="report-actions">
                                <button type="submit" class="report-primary">Generate draft</button>
                            </div>
                        </form>
                    @endif
                </article>
            @empty
                <p>No medical notes have been added.</p>
            @endforelse
        </section>
    </main>
</body>
</html>
