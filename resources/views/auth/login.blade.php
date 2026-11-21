<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>

<body>
    <main>
        <h1>Login as a patient</h1>
        @if (session('status'))
            <p style="color:green" role="status">{{ session('status') }}</p>
        @endif
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <p>
                <label for="email">Email</label><br>
                <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email"
                    maxlength="255" required>
                @error('email')
                    <br><span>{{ $message }}</span>
                @enderror
            </p>

            <p>
                <label for="password">Password (at least 8 characters)</label><br>
                <input type="password" id="password" name="password" autocomplete="new-password" minlength="8"
                    required>
                @error('password')
                    <br><span>{{ $message }}</span>
                @enderror
            </p>

            <button type="submit">Login</button>
        </form>
    </main>
</body>

</html>
