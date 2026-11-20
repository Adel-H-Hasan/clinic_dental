<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit medical note | MedAssist Dental</title>
    <link rel="stylesheet" href="{{ asset('css/medical-reports.css') }}">
</head>
<body class="report-page medical-notes">
    <header class="report-brand">MedAssist <span>Dental</span></header>

    <main class="report-shell">
        <a class="report-back" href="{{ route('doctor.appointments.medical-notes.index', $note->appointment_id) }}">
            Back to medical notes
        </a>

        <article class="report-card">
            <header class="report-card-heading">
                <div>
                    <h1>Edit medical note</h1>
                    <p>Appointment #{{ $note->appointment_id }}</p>
                </div>
            </header>

            @php
                $nextVisit = '';

                if ($note->next_visit) {
                    $nextVisit = str_replace(' ', 'T', substr($note->next_visit, 0, 16));
                }
            @endphp

            <form method="POST" class="note-form"
                  action="{{ route('doctor.appointments.medical-notes.update', [$note->appointment_id, $note->id]) }}">
                @csrf
                @method('PUT')

                <div class="note-fields">
                    <div class="note-field">
                        <label class="report-label" for="diagnosis">Diagnosis</label>
                        <textarea id="diagnosis" name="diagnosis" rows="3" dir="auto" required>{{ old('diagnosis', $note->diagnosis) }}</textarea>
                    </div>

                    <div class="note-field">
                        <label class="report-label" for="prescription">Prescription</label>
                        <textarea id="prescription" name="prescription" rows="3" dir="auto">{{ old('prescription', $note->prescription) }}</textarea>
                    </div>

                    <div class="note-field note-field-wide">
                        <label class="report-label" for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="3" dir="auto">{{ old('notes', $note->notes) }}</textarea>
                    </div>

                    <div class="note-field">
                        <label class="report-label" for="next_visit">Next visit</label>
                        <input id="next_visit" name="next_visit" type="datetime-local" value="{{ old('next_visit', $nextVisit) }}">
                    </div>
                </div>

                @foreach ($errors->all() as $error)
                    <p class="report-error" role="alert">{{ $error }}</p>
                @endforeach

                <div class="report-actions">
                    <button type="submit" class="report-primary">Save changes</button>
                </div>
            </form>
        </article>
    </main>
</body>
</html>
