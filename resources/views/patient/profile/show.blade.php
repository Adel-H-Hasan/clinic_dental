<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Profile</title>
</head>

<body>
    <main>
    <h1>My profile</h1>

    @if (session('success'))
        <p role="status">{{ session('success') }}</p>
    @endif

    <dl>
        <dt>Full name</dt>
        <dd>{{ $patient->name }}</dd>

        <dt>Email address</dt>
        <dd>{{ $patient->email }}</dd>

        <dt>Phone number</dt>
        <dd>{{ $patient->phone }}</dd>

        <dt>Date of birth</dt>
        <dd>{{ $patient->date_of_birth ?? 'Not provided' }}</dd>

        <dt>Gender</dt>
        <dd>{{ $patient->gender ? ucfirst($patient->gender) : 'Not provided' }}</dd>
    </dl>

    <a href="{{ route('patient.profile.edit') }}">Edit my profile</a>
</main>
</body>

</html>
