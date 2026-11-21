<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>

<body>
    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <p>Choose a new password for <strong>{{ $email }}</strong>.</p>

        <input type="hidden" name="email" value="{{ $email }}">
        <input type="hidden" name="token" value="{{ $token }}">

        <label for="password">New password</label>
        <input id="password" type="password" name="password" autocomplete="new-password" required>
        @error('password')
            <p style="color:red">{{ $message }}</p>
        @enderror

        <br>
        
        <label for="password_confirmation">Confirm new password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"
            required>
        @error('password_confirmation')
            <p style="color:red">{{ $message }}</p>
        @enderror

        <button type="submit">Save password</button>
    </form>
</body>

</html>
