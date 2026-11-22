<x-workspace title="{{ __('Edit Patient Profile') }}">
<div class="panel">
    <div class="view-content">


    <form method="POST" action="{{ route('patient.profile.update') }}">
        @csrf
        @method('PATCH')

        <p>
            <label for="name">{{ __('Full name') }}</label><br>
            <input type="text" id="name" name="name" autocomplete="name"
                   value="{{ old('name', $patient->name) }}" maxlength="255" required>
        </p>

        <p>
            <label for="email">{{ __('Email address') }}</label><br>
            <input type="email" id="email" name="email" autocomplete="email"
                   value="{{ old('email', $patient->email) }}" required>
        </p>

        <p>
            <label for="phone">{{ __('Phone number (include country code)') }}</label><br>
            <input type="tel" id="phone" name="phone" autocomplete="tel"
                   value="{{ old('phone', $patient->phone) }}"
                   placeholder="+962791234567" required>
        </p>

        <p>
            <label for="date_of_birth">{{ __('Date of birth (optional)') }}</label><br>
            <input type="date" id="date_of_birth" name="date_of_birth"
                   value="{{ old('date_of_birth', $patient->getRawOriginal('date_of_birth')) }}"
                   max="{{ today()->toDateString() }}">
        </p>

        <p>
            <label for="gender">{{ __('Gender (optional)') }}</label><br>
            <select id="gender" name="gender">
                <option value="">{{ __('Select gender') }}</option>
                <option value="male" @selected(old('gender', $patient->gender) === 'male')>
                    {{ __('Male') }}
                </option>
                <option value="female" @selected(old('gender', $patient->gender) === 'female')>
                    {{ __('Female') }}
                </option>
            </select>
        </p>

        <button type="submit">{{ __('Save changes') }}</button>
        <a href="{{ route('patient.profile.show') }}">{{ __('Cancel') }}</a>
    </form>
</div>

</div>
</x-workspace>
