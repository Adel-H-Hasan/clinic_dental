<x-workspace title="{{ __('Medical notes') }}">
@if(!config('services.gemini.demo_review'))<p class="notice">{{ __('AI note review and report generation are currently unavailable. You can still write and edit clinical notes.') }}</p>@endif<div class="panel">


    <div class="view-content">
        <section class="report-card">
            <header class="report-card-heading">
                <div>

                    <p>{{ __('Appointment #') }}{{ $appointment->id }}</p>
                </div>
                <span class="report-status {{ $appointment->status === 'completed' ? 'note-status-complete' : '' }}">
                    {{ __(ucfirst($appointment->status)) }}
                </span>
            </header>

            @if ($appointment->status === 'completed')
                <form method="POST" class="note-form"
                      action="{{ route('doctor.appointments.medical-notes.store', $appointment->id) }}">
                    @csrf

                    <div class="note-fields">
                        <div class="note-field">
                            <label class="report-label" for="diagnosis">{{ __('Diagnosis') }}</label>
                            <textarea id="diagnosis" name="diagnosis" rows="3" dir="auto" required>{{ old('diagnosis') }}</textarea>
                        </div>

                        <div class="note-field">
                            <label class="report-label" for="prescription">{{ __('Prescription') }}</label>
                            <textarea id="prescription" name="prescription" rows="3" dir="auto">{{ old('prescription') }}</textarea>
                        </div>

                        <div class="note-field note-field-wide">
                            <label class="report-label" for="notes">{{ __('Notes') }}</label>
                            <textarea id="notes" name="notes" rows="3" dir="auto">{{ old('notes') }}</textarea>
                        </div>

                        <div class="note-field">
                            <label class="report-label" for="next_visit">{{ __('Next visit') }}</label>
                            <input id="next_visit" name="next_visit" type="datetime-local" value="{{ old('next_visit') }}">
                        </div>
                    </div>

                    <div class="report-actions">
                        <button type="submit" class="report-primary">{{ __('Save medical note') }}</button>
                    </div>
                </form>
            @else
                <p>{{ __('You can add a note after the visit is completed.') }}</p>
            @endif
        </section>

        <section class="note-list">
            <h2 id="saved-notes">{{ __('Saved notes') }}</h2>
            @php
                $notesPage = request()->only('notes_page');
            @endphp

            @forelse ($medicalNotes as $note)
                <article class="report-card">
                    <dl class="report-details">
                        <div class="note-field-wide">
                            <dt>{{ __('Diagnosis') }}</dt>
                            <dd class="report-text" dir="auto">{{ $note->diagnosis }}</dd>
                        </div>

                        @if ($note->prescription)
                            <div>
                                <dt>{{ __('Prescription') }}</dt>
                                <dd class="report-text" dir="auto">{{ $note->prescription }}</dd>
                            </div>
                        @endif

                        @if ($note->notes)
                            <div>
                                <dt>{{ __('Notes') }}</dt>
                                <dd class="report-text" dir="auto">{{ $note->notes }}</dd>
                            </div>
                        @endif

                        @if ($note->next_visit)
                            <div>
                                <dt>{{ __('Next visit') }}</dt>
                                <dd>{{ $note->next_visit }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($appointment->status === 'completed')
                        <div class="report-actions">
                            <a class="note-secondary"
                               href="{{ route('doctor.appointments.medical-notes.edit', [$appointment->id, $note->id, ...$notesPage]) }}">
                                {{ __('Edit note') }}
                            </a>

                            @if (config('services.gemini.demo_review'))
                                <form method="POST"
                                      action="{{ route('doctor.appointments.medical-notes.review', [$appointment->id, $note->id, ...$notesPage]) }}">
                                    @csrf
                                    <button type="submit" class="note-secondary">{{ __('Review note') }}</button>
                                </form>
                            @endif
                        </div>
                    @endif

                    @php
                        $review = session('medical_note_review');
                    @endphp

                    @if ($review && $review['note_id'] == $note->id)
                    <span id="review-result-{{ $note->id }}"></span>
                        @if ($review['suggestions'] === null)
                            <p class="report-error" role="alert">{{ __('Gemini review is unavailable. Please try again.') }}</p>
                        @elseif (empty($review['suggestions']))
                            <p>{{ __('No missing information or conflicts were found.') }}</p>
                        @else
                            <section class="note-section">
                                <h3>{{ __('Suggestions for the doctor') }}</h3>

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
                                <h3>{{ __('Medical report') }}</h3>
                                <span class="report-status {{ $note->medical_report->status === 'approved' ? 'note-status-complete' : '' }}">
                                    {{ __(ucfirst($note->medical_report->status)) }}
                                </span>
                            </header>
                            <div class="note-inset report-text" dir="auto">{{ $note->medical_report->content }}</div>

                            @if ($note->medical_report->status === 'draft' && $appointment->status === 'completed')
                                <div class="report-actions">
                                    <a class="note-secondary"
                                       href="{{ route('doctor.medical-notes.report.review', [$note->id, ...$notesPage]) }}">{{ __('Review draft') }}</a>
                                </div>
                            @endif
                        </section>
                    @elseif (config('services.gemini.demo_review') && $appointment->status === 'completed')
                        <form method="POST" class="note-section"
                              action="{{ route('doctor.medical-notes.report.store', [$note->id, ...$notesPage]) }}">
                            @csrf

                            <div class="note-field">
                                <label class="report-label" for="doctor-additions-{{ $note->id }}">{{ __('Doctor-confirmed details') }}</label>
                                <textarea id="doctor-additions-{{ $note->id }}" name="doctor_additions" rows="2" dir="auto"
                                          maxlength="2000">{{ session('report_error_note_id') == $note->id ? old('doctor_additions') : '' }}</textarea>
                            </div>

                            @if (session('report_error_note_id') == $note->id)
                                <p class="report-error" role="alert">{{ __('Gemini could not generate a draft. Please try again.') }}</p>
                            @endif

                            <div class="report-actions">
                                <button type="submit" class="report-primary">{{ __('Generate draft') }}</button>
                            </div>
                        </form>
                    @endif
                </article>
            @empty
                <p>{{ __('No medical notes have been added.') }}</p>
            @endforelse
            <div class="pagination">{{ $medicalNotes->links() }}</div>
        </section>
    </div>

</div>
</x-workspace>
