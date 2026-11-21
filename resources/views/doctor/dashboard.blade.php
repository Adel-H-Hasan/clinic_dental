<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
</head>
<body>
    <h1>Welcome Dr. {{ $doctor->user->name }}</h1>
    <p>Speciality: {{ $doctor->speciality->name }}</p>

    <div class="stats-container">

    <div class="stat-card">
        <h3>Today's Appointments</h3>
        <h2>{{ $todayAppointments }}</h2>
    </div>

    <div class="stat-card">
        <h3>Total Appointments</h3>
        <h2>{{ $totalAppointments }}</h2>
    </div>

    <div class="stat-card">
        <h3>Pending Appointments</h3>
        <h2>{{ $pendingAppointments }}</h2>
    </div>

    <div class="stat-card">
        <h3>Total Patients</h3>
        <h2>{{ $totalPatients }}</h2>
    </div>

</div>

<h2>Upcoming Appointments</h2>

<table border="1">
    <thead>
        <tr>
            <th>Patient</th>
            <th>Appointment Type</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($upcomingAppointments as $appointment)
            <tr>
                <td>{{ $appointment->user->name }}</td>
                <td>{{ $appointment->doctor_appointment_type->appointment_type->name }}</td>
                <td>{{ $appointment->scheduled_at->format('Y-m-d') }}</td>
                <td>{{ $appointment->scheduled_at->format('h:i A') }}</td>
                <td>{{ ucfirst($appointment->status) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5">No upcoming appointments.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<h2>My Appointments</h2>
<table border="1">
    <thead>
        <tr>
            <th>Patient</th>
            <th>Appointment Type</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($appointments as $appointment)
        <tr>
            <td>{{ $appointment->user->name}}</td>

            <td>
                {{ $appointment->doctor_appointment_type->appointment_type->name }}
            </td>

            <td>
                {{ $appointment->scheduled_at->format('Y-m-d') }}
            </td>

             <td>
                {{ $appointment->scheduled_at->format('h:i A') }}
             </td>

            <td>{{ ucfirst($appointment->status) }}</td>
        </tr>
        @endforeach


    </tbody>
</table>
    
</body>
</html>