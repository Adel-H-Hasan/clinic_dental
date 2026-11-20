<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Welcome to the Admin Dashboard</h1>
    <p>Manage your dental clinic from here</p>

    <h2>Total Patients:{{ $patientsCount }}</h2>
    <h2>Total Doctors:{{ $doctorsCount }}</h2>
    <h2>Total Appointments:{{ $appointmentsCount }}</h2>
    <h2>Total Specialities:{{ $specialitiesCount }}</h2>

    <h2>Recent Appointments</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Patient Name</th>
                <th>Appointment Date</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse($recentAppointments as $appointment)
            <tr>
                <td>{{ $appointment->id }}</td>
                <td>{{ $appointment->user->name ?? 'Unknown' }}</td>
                <td>{{ $appointment->scheduled_at }}</td>
                <td>{{ $appointment->status }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4">No Appointments found</td>
            </tr>
            @endforelse
            
        </tbody>
    </table>

    <h2>Appointments By Status</h2>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
            @forelse($appointmentsByStatus as $item)
            <tr>
                <td>{{ ucfirst($item->status) }}</td>
                <td>{{ $item->total }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2">No appointment Statistics available</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Recent registered Users</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Registered At</th>
            </tr>
        </thead>

        <tbody>
            @forelse($recentUsers as $user)
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ ucfirst($user->role) }}</td>
                <td>{{ $user->created_at }}</td>
            @empty
            <tr>
                <td colspan="5">No users found.</td>
            </tr>
        @endforelse
        </tbody>

    </table>


    <h2>Recent Patients</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Registered At</th>
        </tr>
    </thead>

    <tbody>
        @forelse($recentPatients as $patient)
            <tr>
                <td>{{ $patient->id }}</td>
                <td>{{ $patient->name }}</td>
                <td>{{ $patient->email }}</td>
                <td>{{ $patient->created_at }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No patients found.</td>
            </tr>
        @endforelse
    </tbody>

    <h2>Upcoming Appointments</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Patient</th>
            <th>Scheduled At</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>
        @forelse($upcomingAppointments as $appointment)
            <tr>
                <td>{{ $appointment->id }}</td>
                <td>{{ $appointment->user->name ?? 'Unknown' }}</td>
                <td>{{ $appointment->scheduled_at }}</td>
                <td>{{ ucfirst($appointment->status) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No upcoming appointments found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
</table>
</body>
</html>