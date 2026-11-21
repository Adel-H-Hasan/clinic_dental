<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Patient Profile</title>
</head>
<body>
    <main>
    <h1>Edit my profile</h1>

    <form method="POST" action="{{ route('patient.profile.update') }}">
        @csrf
        @method('PATCH')

        <p>
            <label for="name">Full name</label><br>
            <input type="text" id="name" name="name" autocomplete="name"
                   value="{{ old('name', $patient->name) }}" maxlength="255" required>
            @error('name')
                <br><span>{{ $message }}</span>
            @enderror
        </p>

        <p>
            <label for="email">Email address</label><br>
            <input type="email" id="email" name="email" autocomplete="email"
                   value="{{ old('email', $patient->email) }}" required>
            @error('email')
                <br><span>{{ $message }}</span>
            @enderror
        </p>

        <p>
            <label for="phone">Phone number (include country code)</label><br>
            <input type="tel" id="phone" name="phone" autocomplete="tel"
                   value="{{ old('phone', $patient->phone) }}"
                   placeholder="+962791234567" required>
            @error('phone')
                <br><span>{{ $message }}</span>
            @enderror
        </p>

        <p>
            <label for="date_of_birth">Date of birth (optional)</label><br>
            <input type="date" id="date_of_birth" name="date_of_birth"
                   value="{{ old('date_of_birth', $patient->getRawOriginal('date_of_birth')) }}"
                   max="{{ today()->toDateString() }}">
            @error('date_of_birth')
                <br><span>{{ $message }}</span>
            @enderror
        </p>

        <p>
            <label for="gender">Gender (optional)</label><br>
            <select id="gender" name="gender">
                <option value="">Select gender</option>
                <option value="male" @selected(old('gender', $patient->gender) === 'male')>
                    Male
                </option>
                <option value="female" @selected(old('gender', $patient->gender) === 'female')>
                    Female
                </option>
            </select>
            @error('gender')
                <br><span>{{ $message }}</span>
            @enderror
        </p>

        <button type="submit">Save changes</button>
        <a href="{{ route('patient.profile.show') }}">Cancel</a>
    </form>
</main>
</body>
</html>
