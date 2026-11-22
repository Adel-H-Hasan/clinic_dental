<x-workspace title="{{ __('Doctor Details') }}">
<div class="panel">
    <div class="view-content">
        <a href="{{ route('admin.doctors.index') }}">{{ __('Back to doctors') }}</a>


        @if (session('status'))
            <p style="color:green" role="status">{{ __(session('status')) }}</p>
        @endif
        <p>{{ $doctor->speciality->name }}</p>

        <dl>
            <dt>{{ __('License number') }}</dt>
            <dd>{{ $doctor->license_number }}</dd>

            <dt>{{ __('Bio') }}</dt>
            <dd>{{ $doctor->bio }}</dd>

            <dt>{{ __('Years of experience') }}</dt>
            <dd>{{ $doctor->years_experience }}</dd>

            <dt>{{ __('Consultation fee') }}</dt>
            <dd>{{ number_format($doctor->consultation_fee, 2) }}</dd>
        </dl>
        <a href="{{ route('admin.doctors.edit', $doctor->id) }}">{{ __('Edit doctor') }}</a>
        @if ($doctor->is_active)
            <form method="POST" action="{{ route('admin.doctors.destroy', $doctor->id) }}"
                onsubmit="return confirm(@js(__('Deactivate this doctor profile?')))">
                @csrf
                @method('DELETE')

                <button type="submit">{{ __('Deactivate doctor') }}</button>
            </form>
        @endif
    </div>

</div>
</x-workspace>
