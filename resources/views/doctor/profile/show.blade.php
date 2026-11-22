<x-workspace title="{{ __('Doctor Profile') }}">
<div class="panel">
    <div class="view-content">


        <h2>{{ $doctor->name }}</h2>
        @if ($profile->image)
            <img src="{{ asset('storage/' . $profile->image) }}" alt="Profile photo of {{ $doctor->name }}" width="150">
        @endif
        <p>{{ $profile->speciality->name }}</p>

        <dl>
            <dt>{{ __('Email') }}</dt>
            <dd>{{ $doctor->email }}</dd>

            <dt>{{ __('Phone') }}</dt>
            <dd>{{ $doctor->phone }}</dd>

            <dt>{{ __('License number') }}</dt>
            <dd>{{ $profile->license_number }}</dd>

            <dt>{{ __('Bio') }}</dt>
            <dd>{{ $profile->bio }}</dd>

            <dt>{{ __('Years of experience') }}</dt>
            <dd>{{ $profile->years_experience }}</dd>

            <dt>{{ __('Consultation fee') }}</dt>
            <dd>{{ number_format($profile->consultation_fee, 2) }}</dd>

            <dt>{{ __('Profile status') }}</dt>
            <dd>{{ __($profile->is_active ? 'Active' : 'Inactive') }}</dd>
        </dl>

        <a href="{{ route('doctor.profile.edit') }}">{{ __('Edit my profile') }}</a>
    </div>

</div>
</x-workspace>
