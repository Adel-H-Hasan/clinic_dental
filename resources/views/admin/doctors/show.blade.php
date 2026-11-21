<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Details</title>
</head>

<body>
    <main>
        <a href="{{ route('admin.doctors.index') }}">Back to doctors</a>

        <h1>{{ $doctor->user?->name ?? 'Doctor profile' }}</h1>
        @if (session('status'))
            <p style="color:green" role="status">{{ session('status') }}</p>
        @endif
        <p>{{ $doctor->speciality->name }}</p>

        <dl>
            <dt>License number</dt>
            <dd>{{ $doctor->license_number }}</dd>

            <dt>Bio</dt>
            <dd>{{ $doctor->bio }}</dd>

            <dt>Years of experience</dt>
            <dd>{{ $doctor->years_experience }}</dd>

            <dt>Consultation fee</dt>
            <dd>{{ number_format($doctor->consultation_fee, 2) }}</dd>
        </dl>
        <a href="{{ route('admin.doctors.edit', $doctor->id) }}">Edit doctor</a>
        @if ($doctor->is_active)
            <form method="POST" action="{{ route('admin.doctors.destroy', $doctor->id) }}"
                onsubmit="return confirm('Deactivate this doctor profile?')">
                @csrf
                @method('DELETE')

                <button type="submit">Deactivate doctor</button>
            </form>
        @endif
    </main>
</body>

</html>
