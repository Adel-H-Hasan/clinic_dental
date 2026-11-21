<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use App\Models\Specialities;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $doctors = DoctorProfile::with(['user', 'speciality'])
            ->get();

        return view('admin.doctors.index', compact('doctors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $specialities = Specialities::orderBy('name')->get(['id', 'name']);

        return view('admin.doctors.create', compact('specialities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'max:20', 'regex:/\A\+[1-9][0-9]{1,14}\z/'],

            'speciality_id' => ['required', 'integer', Rule::exists('specialities', 'id')],
            'license_number' => ['required', 'string', 'max:255', Rule::unique('doctor_profiles', 'license_number')],
            'bio' => ['required', 'string'],
            'consultation_fee' => ['required', 'numeric', 'decimal:0,2', 'between:0,999999.99'],
        ]);

        // A database transaction groups several saves so they succeed or fail together.
        $doctor = DB::transaction(function () use ($data) {
            $user = new User();
            $user['name'] = $data['name'];
            $user['email'] = $data['email'];
            $user['phone'] = $data['phone'];
            $user['role'] = 'doctor';
            $user['password'] = Str::random(64);
            $user->save();

            //? $user->doctor_profile()->create([...]):
            //  automatically writes that ID into doctor_profiles.user_id through hasOne relationship.

            $user->doctor_profile()->create([
                'speciality_id' => $data['speciality_id'],
                'license_number' => $data['license_number'],
                'bio' => $data['bio'],
                'consultation_fee' => $data['consultation_fee'],

            ]);

            return $user;
        });

        // send the doctor a link to choose their password.
        try {
            $status = Password::sendResetLink(['email' => $doctor->email]);

            $linkSent = $status === Password::ResetLinkSent;
        } catch (TransportExceptionInterface $exception) {
            report($exception);
            $linkSent = false;
        }

        $message = $linkSent
            ? 'Doctor account created. A password setup link was sent by email.'
            : 'Doctor account created. The doctor can request a new link from Forgot Password.';

        return redirect()->route('admin.doctors.index')->with('status', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $doctor = DoctorProfile::with(['user', 'speciality'])
            ->findOrFail($id);

        return view('admin.doctors.show', compact('doctor'));
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $doctor = DoctorProfile::with('user')->findOrFail($id);
        $specialities = Specialities::orderBy('name')->get(['id', 'name']);

        return view('admin.doctors.edit', compact('doctor', 'specialities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $doctor = DoctorProfile::findOrFail($id);

        $data = $request->validate([
            'speciality_id' => ['required', 'integer', Rule::exists('specialities', 'id')],
            'license_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('doctor_profiles', 'license_number')->ignore($doctor),
            ],
            'bio' => ['required', 'string'],
            'years_experience' => ['required', 'integer', 'between:0,255'],
            'consultation_fee' => ['required', 'numeric', 'decimal:0,2', 'between:0,999999.99'],
            'is_active' => ['required', 'boolean'],
        ]);

        $doctor->is_active = $request->boolean('is_active');
        unset($data['is_active']);
        // copies the remaining validated fields
        $doctor->fill($data);
        $doctor->save();

        return redirect()->route('admin.doctors.show', $doctor->id)
            ->with('status', 'Doctor profile updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $doctor = DoctorProfile::findOrFail ($id);
        $doctor['is_active'] = false;
        $doctor->save ();

        return redirect()->route('admin.doctors.index')
        ->with('status', 'Doctor profile deactivated.');
    }
}
