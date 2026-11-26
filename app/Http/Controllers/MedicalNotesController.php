<?php

namespace App\Http\Controllers;

use App\Models\Appointments;
use App\Models\MedicalNotes;
use App\Services\GeminiNoteReviewer;
use Illuminate\Http\Request;

class MedicalNotesController extends Controller
{
    public function index(Request $request, $id)
    {
        $doctor = $request->user()->doctor_profile;
        $appointment = $this->findDoctorAppointment($doctor, $id);

        $medicalNotes = $appointment->medical_notes()
            ->where('doctor_profile_id', $doctor->id)
            ->with('medical_report')
            ->orderByDesc('id')
            ->paginate(5, pageName: 'notes_page')
            ->fragment('saved-notes');

        return view('doctor.medical-notes.index', [
            'appointment' => $appointment,
            'medicalNotes' => $medicalNotes,
        ]);
    }

    public function store(Request $request, $id)
    {
        $doctor = $request->user()->doctor_profile;
        $appointment = $this->findDoctorAppointment($doctor, $id);

        if ($appointment->status !== 'completed') {
            abort(403, 'Complete the visit before adding a medical note.');
        }

        $data = $this->validateNote($request);

        $note = new MedicalNotes();
        $note->doctor_profile_id = $doctor->id;
        $note->diagnosis = $data['diagnosis'];
        $note->prescription = $data['prescription'] ?? null;
        $note->notes = $data['notes'] ?? null;

        if (!empty($data['next_visit'])) {
            $note->next_visit = str_replace('T', ' ', $data['next_visit']) . ':00';
        }

        $appointment->medical_notes()->save($note);

        return redirect()->route('doctor.appointments.medical-notes.index', $id);
    }

    public function edit(Request $request, $id, $noteId)
    {
        $note = $this->findEditableNote($request, $id, $noteId);

        return view('doctor.medical-notes.edit', ['note' => $note]);
    }

    public function update(Request $request, $id, $noteId)
    {
        $note = $this->findEditableNote($request, $id, $noteId);

        $data = $this->validateNote($request);

        $note->diagnosis = $data['diagnosis'];
        $note->prescription = $data['prescription'] ?? null;
        $note->notes = $data['notes'] ?? null;
        $note->next_visit = null;

        if (!empty($data['next_visit'])) {
            $note->next_visit = str_replace('T', ' ', $data['next_visit']) . ':00';
        }

        $note->save();

        return redirect()->route('doctor.appointments.medical-notes.index', [$id, ...$request->only('notes_page')]);
    }

    public function review(Request $request, $id, $noteId, GeminiNoteReviewer $reviewer)
    {
        if (!config('services.gemini.demo_review')) {
            abort(404);
        }

        $note = $this->findEditableNote($request, $id, $noteId);

        $suggestions = $reviewer->review($note);

        return redirect()
            ->to(
                route('doctor.appointments.medical-notes.index', [$id, ...$request->only('notes_page')])
                    . '#review-result-' . $note->id
            )
            ->with('medical_note_review', [
                'note_id' => $note->id,
                'suggestions' => $suggestions,
            ]);
    }
    private function findEditableNote(Request $request, $id, $noteId)
    {
        $doctor = $request->user()->doctor_profile;
        $appointment = $this->findDoctorAppointment($doctor, $id);

        if ($appointment->status !== 'completed') {
            abort(403, 'Complete the visit before editing a medical note.');
        }

        $note = $appointment->medical_notes()
            ->where('doctor_profile_id', $doctor->id)
            ->find($noteId);

        if ($note === null) {
            abort(404);
        }

        return $note;
    }

    private function validateNote(Request $request)
    {
        return $request->validate([
            'diagnosis' => 'required|string',
            'prescription' => 'nullable|string',
            'notes' => 'nullable|string',
            'next_visit' => 'nullable|date_format:Y-m-d\TH:i',
        ]);
    }

    private function findDoctorAppointment($doctor, $id)
    {
        if ($doctor === null) {
            abort(403);
        }

        $appointment = Appointments::whereRelation(
            'doctor_appointment_type',
            'doctor_profile_id',
            $doctor->id
        )->find($id);

        if ($appointment === null) {
            abort(404);
        }

        return $appointment;
    }
}
