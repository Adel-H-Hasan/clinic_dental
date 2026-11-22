<x-workspace title="{{ __('Patient Profile') }}">
<div class="panel">
    <div class="view-content">


    @if (session('success'))
        <p role="status">{{ __(session('success')) }}</p>
    @endif

    <dl>
        <dt>{{ __('Full name') }}</dt>
        <dd>{{ $patient->name }}</dd>

        <dt>{{ __('Email address') }}</dt>
        <dd>{{ $patient->email }}</dd>

        <dt>{{ __('Phone number') }}</dt>
        <dd>{{ $patient->phone }}</dd>

        <dt>{{ __('Date of birth') }}</dt>
        <dd>{{ $patient->date_of_birth ?? __('Not provided') }}</dd>

        <dt>{{ __('Gender') }}</dt>
        <dd>{{ $patient->gender ? __(ucfirst($patient->gender)) : __('Not provided') }}</dd>
    </dl>

    <a href="{{ route('patient.profile.edit') }}">{{ __('Edit my profile') }}</a>
</div>

</div>
</x-workspace>
