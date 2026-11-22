<x-workspace title="{{ __('Clinical appointments') }}">
<p>{{ __('Confirm upcoming requests. Complete visits after their scheduled end to add clinical notes.') }}</p>
<div class="workspace-table" tabindex="0" role="region" aria-label="{{ __('Clinical appointments') }}">
<table><thead><tr><th scope="col">{{ __('Time') }}</th><th scope="col">{{ __('Patient') }}</th><th scope="col">{{ __('Service') }}</th><th scope="col">{{ __('Status') }}</th><th scope="col">{{ __('Actions') }}</th></tr></thead><tbody>
@forelse($appointments as $appointment)
<tr><td>{{ $appointment->scheduled_at->locale(app()->getLocale())->translatedFormat('d M Y, H:i') }}</td><td>{{ $appointment->user->name }}<br>{{ $appointment->reason }}</td><td>{{ $appointment->doctor_appointment_type->appointment_type->name }}</td><td>{{ __(ucfirst($appointment->status)) }}</td>
<td>
@if($appointment->status === 'pending' && $appointment->scheduled_at->isFuture())
<form method="post" action="{{ route('doctor.appointments.status', $appointment->id) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="confirmed"><button>{{ __('Confirm visit #') }}{{ $appointment->id }}</button></form>
@elseif($appointment->status === 'confirmed' && $appointment->scheduled_at->copy()->addMinutes($appointment->doctor_appointment_type->duration_minutes)->lessThanOrEqualTo(now()))
<form method="post" action="{{ route('doctor.appointments.status', $appointment->id) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="completed"><button>{{ __('Complete visit #') }}{{ $appointment->id }}</button></form>
@endif
<a href="{{ route('doctor.appointments.medical-notes.index', $appointment->id) }}">{{ __('Clinical notes #') }}{{ $appointment->id }}</a>
</td></tr>
@empty<tr><td colspan="5">{{ __('No appointments assigned to you.') }}</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $appointments->links() }}</div>
</x-workspace>
