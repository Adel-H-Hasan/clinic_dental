<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor</title>
</head>

<body>
    <h1>Edit Dr. {{ $doctor->user?->name ?? 'doctor profile' }} Information</h1>
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('admin.doctors.update', $doctor->id) }}">
        @csrf
        @method('PATCH')

        <div>
            <label for="speciality_id">Speciality</label>
            <select id="speciality_id" name="speciality_id" required>
                @foreach ($specialities as $speciality)
                    <option value="{{ $speciality->id }}" @selected(old('speciality_id', $doctor->speciality_id) == $speciality->id)>
                        {{ $speciality->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="license_number">License number</label>
            <input id="license_number" name="license_number" type="text"
                value="{{ old('license_number', $doctor->license_number) }}" required>
        </div>

        <div>
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" rows="3" required>{{ old('bio', $doctor->bio) }}</textarea>
        </div>

        <div>
            <label for="years_experience">Years of experience</label>
            <input id="years_experience" name="years_experience" type="number" min="0" max="255"
                value="{{ old('years_experience', $doctor->years_experience) }}" required>
        </div>

        <div>
            <label for="consultation_fee">Consultation fee</label>
            <input id="consultation_fee" name="consultation_fee" type="number" min="0" max="999999.99"
                step="0.01" value="{{ old('consultation_fee', $doctor->consultation_fee) }}" required>
        </div>
        <div>
            <label for="is_active">Profile status</label>
            <select id="is_active" name="is_active" required>
                <option value="1" @selected(old('is_active', $doctor->is_active) == 1)>
                    Active
                </option>
                <option value="0" @selected(old('is_active', $doctor->is_active) == 0)>
                    Inactive
                </option>
            </select>
        </div>
        <button type="submit">Save changes</button>
        <a href="{{ route('admin.doctors.show', $doctor->id) }}">Cancel</a>
    </form>
</body>

</html>
