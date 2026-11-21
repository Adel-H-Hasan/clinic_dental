<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
</head>
<body>
    <main>
        <h1>Register as a patient</h1>
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <p>
                <label for="name">Full name</label><br>
                <input type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="255" required>
                @error('name')
                    <br><span>{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="email">Email</label><br>
                <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required>
                @error('email')
                    <br><span>{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="phone">Phone number</label><br>
                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" maxlength="20" required>
                @error('phone')
                    <br><span>{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="date_of_birth">Date of birth (optional)</label><br>
                <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" autocomplete="bday" max="{{ now()->toDateString() }}">
                @error('date_of_birth')
                    <br><span>{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="gender">Gender (optional)</label><br>
                <select id="gender" name="gender">
                    <option value="">Select gender</option>
                    <option value="male" @selected(old('gender') === 'male')>Male</option>
                    <option value="female" @selected(old('gender') === 'female')>Female</option>
                </select>
                @error('gender')
                    <br><span>{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="password">Password (at least 8 characters)</label><br>
                <input type="password" id="password" name="password" autocomplete="new-password" minlength="8" required>
                @error('password')
                    <br><span>{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="password_confirmation">Confirm password</label><br>
                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
            </p>

            <button type="submit">Create account</button>
        </form>
    </main>
</body>
</html>
