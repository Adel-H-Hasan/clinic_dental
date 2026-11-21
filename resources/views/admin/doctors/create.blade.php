<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Doctor</title>
</head>

<body>
    <form method="POST" action="{{ route('admin.doctors.store') }}">
        @csrf

        {{-- User fields --}}
        <div>
            <label for="name">Full name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required>
        </div>

        <div>
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required>
        </div>

        <div>
            <label for="phone">Phone number</label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required>
        </div>

        <label for="speciality_id">Speciality</label>
        <select id="speciality_id" name="speciality_id" required>
            <option value="">Choose a speciality</option>
            @foreach ($specialities as $speciality)
                <option value="{{ $speciality->id }}" @selected(old('speciality_id') == $speciality->id)>
                    {{ $speciality->name }}
                </option>
            @endforeach
        </select>

        {{-- Doctor profile --}}
        <div>
            <label for="license_number">License number</label>
            <input id="license_number" name="license_number" type="text" value="{{ old('license_number') }}"
                required>
        </div>

        <div>
            <label for="bio">Doctor bio</label>
            <textarea id="bio" name="bio" rows="3" required>{{ old('bio') }}</textarea>
        </div>

        <div>
            <label for="consultation_fee">Consultation fee</label>
            <input id="consultation_fee" name="consultation_fee" type="number" min="0" max="999999.99"
                step="0.01" value="{{ old('consultation_fee') }}" required>
        </div>

        <button type="submit">Create doctor</button>
    </form>

</body>

</html>
