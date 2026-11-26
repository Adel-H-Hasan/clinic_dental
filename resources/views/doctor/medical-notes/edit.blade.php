<x-workspace title="{{ __('Edit medical note') }}">
<div class="panel">


    <div class="view-content">
        @php
            $notesPage = request()->only('notes_page');
        @endphp
        <a class="report-back" href="{{ route('doctor.appointments.medical-notes.index', [$note->appointment_id, ...$notesPage]) }}">
            {{ __('Back to medical notes') }}
        </a>

        <article class="report-card">
            <header class="report-card-heading">
                <div>

                    <p>{{ __('Appointment #') }}{{ $note->appointment_id }}</p>
                </div>
            </header>

            @php
                $nextVisit = '';

                if ($note->next_visit) {
                    $nextVisit = str_replace(' ', 'T', substr($note->next_visit, 0, 16));
                }
            @endphp

            <form method="POST" class="note-form"
                  action="{{ route('doctor.appointments.medical-notes.update', [$note->appointment_id, $note->id, ...$notesPage]) }}">
                @csrf
                @method('PUT')

                <div class="note-fields">
                    <div class="note-field">
                        <label class="report-label" for="diagnosis">{{ __('Diagnosis') }}</label>
                        <textarea id="diagnosis" name="diagnosis" rows="3" dir="auto" required>{{ old('diagnosis', $note->diagnosis) }}</textarea>
                    </div>

                    <div class="note-field">
                        <label class="report-label" for="prescription">{{ __('Prescription') }}</label>
                        <textarea id="prescription" name="prescription" rows="3" dir="auto">{{ old('prescription', $note->prescription) }}</textarea>
                    </div>

                    <div class="note-field note-field-wide">
                        <label class="report-label" for="notes">{{ __('Notes') }}</label>
                        <textarea id="notes" name="notes" rows="3" dir="auto">{{ old('notes', $note->notes) }}</textarea>
                    </div>

                    <div class="note-field">
                        <label class="report-label" for="next_visit">{{ __('Next visit') }}</label>
                        <input id="next_visit" name="next_visit" type="datetime-local" value="{{ old('next_visit', $nextVisit) }}">
                    </div>
                </div>

                <div class="report-actions">
                    <button type="submit" class="report-primary">{{ __('Save changes') }}</button>
                </div>
            </form>
        </article>
    </div>

</div>
</x-workspace>
