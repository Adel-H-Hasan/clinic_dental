<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Profile</title>
</head>

<body>
    <main>
        <h1>My profile</h1>

        <h2>{{ $doctor->name }}</h2>
        @if ($profile->image)
            <img src="{{ asset('storage/' . $profile->image) }}" alt="Profile photo of {{ $doctor->name }}" width="150">
        @endif
        <p>{{ $profile->speciality->name }}</p>

        <dl>
            <dt>Email</dt>
            <dd>{{ $doctor->email }}</dd>

            <dt>Phone</dt>
            <dd>{{ $doctor->phone }}</dd>

            <dt>License number</dt>
            <dd>{{ $profile->license_number }}</dd>

            <dt>Bio</dt>
            <dd>{{ $profile->bio }}</dd>

            <dt>Years of experience</dt>
            <dd>{{ $profile->years_experience }}</dd>

            <dt>Consultation fee</dt>
            <dd>{{ number_format($profile->consultation_fee, 2) }}</dd>

            <dt>Profile status</dt>
            <dd>{{ $profile->is_active ? 'Active' : 'Inactive' }}</dd>
        </dl>

        <a href="{{ route('doctor.profile.edit') }}">Edit my profile</a>
    </main>
</body>

</html>
