<?php

namespace App\Http\Controllers;

use App\Models\DoctorProfile;
use App\Models\DoctorSchedules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorSchedulesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $doctor = $user->doctor_profile;

        if ($doctor === null) {
            abort(403, 'A doctor profile is required.');
        }

        $schedules = $doctor->doctor_schedules()
            ->orderBy('start_time')
            ->get();

        $schedulesByDay = $schedules->groupBy('day_of_week');

        return view('doctor.schedules.index', [
            'schedulesByDay' => $schedulesByDay,
        ]);
    }

    public function store(Request $request)
    {
        $doctor = $request->user()->doctor_profile;

        if ($doctor === null) {
            abort(403, 'A doctor profile is required.');
        }

        $data = $request->validate([
            'day_of_week' => 'required|integer|min:0|max:6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $startTime = $data['start_time'].':00';
        $endTime = $data['end_time'].':00';

        return DB::transaction(function () use ($doctor, $data, $startTime, $endTime) {
            $doctor->newQuery()
                ->whereKey($doctor->id)
                ->lockForUpdate()
                ->firstOrFail();

            $hasOverlap = $doctor->doctor_schedules()
                ->where('day_of_week', $data['day_of_week'])
                ->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime)
                ->exists();

            if ($hasOverlap) {
                return back()
                    ->withErrors(['start_time' => 'This period overlaps an existing schedule.'])
                    ->withInput();
            }

            $schedule = new DoctorSchedules();
            $schedule->day_of_week = $data['day_of_week'];
            $schedule->start_time = $startTime;
            $schedule->end_time = $endTime;
            $schedule->is_available = true;

            $doctor->doctor_schedules()->save($schedule);

            return redirect()->route('doctor.schedules.index');
        }, 3);
    }

    public function edit(Request $request, $id)
    {
        $doctor = $request->user()->doctor_profile;

        if ($doctor === null) {
            abort(403);
        }

        $schedule = $doctor->doctor_schedules()->find($id);

        if ($schedule === null) {
            abort(404);
        }

        return view('doctor.schedules.edit', ['schedule' => $schedule]);
    }

    public function update(Request $request, $id)
    {
        $doctor = $request->user()->doctor_profile;

        if ($doctor === null) {
            abort(403);
        }

        $data = $request->validate([
            'day_of_week' => 'required|integer|min:0|max:6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_available' => 'required|boolean',
        ]);

        $startTime = $data['start_time'] . ':00';
        $endTime = $data['end_time'] . ':00';

        return DB::transaction(function () use ($doctor, $id, $data, $startTime, $endTime) {
            DoctorProfile::lockForUpdate()->findOrFail($doctor->id);

            $schedule = $doctor->doctor_schedules()->find($id);

            if ($schedule === null) {
                abort(404);
            }

            $hasOverlap = $doctor->doctor_schedules()
                ->where('id', '!=', $schedule->id)
                ->where('day_of_week', $data['day_of_week'])
                ->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime)
                ->exists();

            if ($hasOverlap) {
                return back()
                    ->withErrors(['start_time' => 'This period overlaps an existing schedule.'])
                    ->withInput();
            }

            $schedule->day_of_week = $data['day_of_week'];
            $schedule->start_time = $startTime;
            $schedule->end_time = $endTime;
            $schedule->is_available = $data['is_available'];
            $schedule->save();

            return redirect()->route('doctor.schedules.index');
        }, 3);
    }

    public function destroy(Request $request, $id)
    {
        $doctor = $request->user()->doctor_profile;

        if ($doctor === null) {
            abort(403);
        }

        $schedule = $doctor->doctor_schedules()->find($id);

        if ($schedule === null) {
            abort(404);
        }

        $schedule->delete();

        return redirect()->route('doctor.schedules.index');
    }
}
