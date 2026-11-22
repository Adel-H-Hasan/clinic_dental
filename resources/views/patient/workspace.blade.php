<x-workspace title="{{ __('Your appointments') }}">
<p>{{ __('Book care, manage upcoming visits, and read reports approved by your doctor.') }}</p>
<p><a class="button" href="{{ route('patient.booking') }}">{{ __('Book an appointment') }}</a></p>
<section aria-labelledby="appointments-title">
<h2 id="appointments-title">{{ __('Appointment history') }}</h2>
<div class="workspace-table" tabindex="0" role="region" aria-label="{{ __('Appointments') }}">
<table><thead><tr><th scope="col">{{ __('Visit') }}</th><th scope="col">{{ __('Doctor / service') }}</th><th scope="col">{{ __('Status') }}</th><th scope="col">{{ __('Manage') }}</th></tr></thead><tbody>
@forelse($appointments as $appointment)
<tr><td>{{ $appointment->scheduled_at->locale(app()->getLocale())->translatedFormat('d M Y, H:i') }}<br>#{{ $appointment->id }}</td>
<td>{{ $appointment->doctor_appointment_type->doctor_profile->user->name }}<br>{{ $appointment->doctor_appointment_type->appointment_type->name }}</td>
<td><x-badge :status="$appointment->status"/></td>
<td><a href="{{ route('patient.visits.show', $appointment->id) }}">{{ __('View details') }}</a>
@if(in_array($appointment->status, ['pending','confirmed'], true) && $appointment->scheduled_at->isFuture())
<details><summary>{{ __('Reschedule visit #') }}{{ $appointment->id }}</summary>
<form method="post" action="{{ route('patient.appointments.reschedule', $appointment->id) }}">@csrf @method('PATCH')
<label for="reschedule-{{ $appointment->id }}">{{ __('New date and time') }}</label><input id="reschedule-{{ $appointment->id }}" type="datetime-local" name="scheduled_at" required>
<button>{{ __('Request new time') }}</button></form></details>
<details><summary>{{ __('Cancel visit #') }}{{ $appointment->id }}</summary><p>{{ __('This releases your appointment time.') }}</p>
<form method="post" action="{{ route('patient.appointments.cancel', $appointment->id) }}">@csrf @method('PATCH')<button>{{ __('Confirm cancellation') }}</button></form></details>
@endif
</td></tr>
@empty<tr><td colspan="4">{{ __('You have no appointments yet.') }}</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $appointments->links() }}</div>
</section>
<section aria-labelledby="reports-title"><h2 id="reports-title">{{ __('Your approved reports') }}</h2>
<ul>@forelse($reports as $report)<li><a href="{{ route('patient.reports.show', $report->id) }}">{{ __('Read report #') }}{{ $report->id }}</a></li>@empty<li>{{ __('No approved reports yet.') }}</li>@endforelse</ul>
</section>
</x-workspace>

