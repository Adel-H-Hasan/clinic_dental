<x-workspace
    :title="__('Appointment #:id', ['id' => $appointment->id])"
    subtitle="{{ __('Everything you need to know about your visit.') }}">

    <section>

        <x-badge :status="$appointment->status"/>

        <dl>
            <dt>{{ __('Dentist') }}</dt>
            <dd>{{ $appointment->doctor_appointment_type->doctor_profile->user->name }}</dd>

            <dt>{{ __('Service') }}</dt>
            <dd>{{ $appointment->doctor_appointment_type->appointment_type->name }}</dd>

            <dt>{{ __('Time') }}</dt>
            <dd>{{ $appointment->scheduled_at->locale(app()->getLocale())->translatedFormat('l, d F Y · H:i') }}
                ({{ config('app.timezone') }})
            </dd>

            <dt>{{ __('Reason') }}</dt>
            <dd>{{ $appointment->reason ?: __('Not provided') }}</dd>

            <dt>{{ __('Requested') }}</dt>
            <dd>{{ $appointment->created_at->locale(app()->getLocale())->translatedFormat('d M Y · H:i') }}</dd>

            <dt>Price</dt>
            <dd>
                {{ number_format($appointment->doctor_appointment_type->price, 3) }} JOD
            </dd>
        </dl>

        @if($appointment->payments()->where('status', 'paid')->exists())

            <span style="color: green; font-weight: bold;">
                ✓ Paid
            </span>

        @elseif(in_array($appointment->status, ['pending', 'confirmed']))

            <form action="{{ route('patient.payment.checkout', $appointment->id) }}"
                  method="POST">

                @csrf

                <button type="submit" class="button">
                    Pay Now
                </button>

            </form>

        @endif

        <a class="button" href="{{ route('patient.workspace') }}">
            {{ __('Manage appointments') }}
        </a>

    </section>

</x-workspace>