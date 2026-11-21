<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctors List</title>
</head>

<body>
    <main>
        <h1>Doctors</h1>

        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif

        <p>
            <a href="{{ route('admin.doctors.create') }}">Add doctor</a>
        </p>

        @if ($doctors->isEmpty())
            <p>No active doctor profiles to display.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th scope="col">Full name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Speciality</th>
                        <th scope="col">License number</th>
                        <th scope="col">Consultation fee</th>
                        <th scope="col">Profile status</th>
                        <th scope="col">actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($doctors as $doctor)
                        <tr>
                            <td>
                                <a href="{{ route ('admin.doctors.show', $doctor->id) }}">
                                    {{ $doctor->user?->name ?? 'Account unavailable' }}
                                </a>
                            </td>
                            <td>{{ $doctor->user?->email ?? '—' }}</td>
                            <td>{{ $doctor->user?->phone ?? '—' }}</td>
                            <td>{{ $doctor->speciality->name }}</td>
                            <td>{{ $doctor->license_number }}</td>
                            <td>{{ number_format($doctor->consultation_fee, 2) }}</td>
                            <td>{{ $doctor->is_active ? 'Active' : 'Inactive' }}</td>
                            <td><a href="{{ route('admin.doctors.edit', $doctor->id) }}">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </main>
</body>

</html>
