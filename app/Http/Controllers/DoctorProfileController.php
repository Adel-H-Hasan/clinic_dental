<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DoctorProfileController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $doctor = $request->user();

        $profile = $doctor->doctor_profile()
            ->with('speciality')
            ->firstOrFail();

        return view('doctor.profile.show', compact('doctor', 'profile'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $profile = $request->user()->doctor_profile()->firstOrFail();

        return view('doctor.profile.edit', compact('profile'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $profile = $request->user()->doctor_profile()->firstOrFail();
        // [sometimes] means Laravel checks the image field only if it’s present in the request.
        $data = $request->validate([
            'bio' => ['required', 'string'],
            'years_experience' => ['required', 'integer', 'between:0,255'],
            'image' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $oldImagePath = $profile->image;
        $newImagePath = null;

        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('doctors', 'public');
            if ($newImagePath === false) {
                return back()
                    ->withErrors(['image' => 'The photo could not be uploaded. Please try again.'])
                    ->withInput();
                //withInput() tells Laravel to remember the submitted form values for the next request.
            }
            $data['image'] = $newImagePath;
        }

        try {
            $profile->update($data);
        } catch (\Throwable $exception) { //=> \ Global namespace
            if ($newImagePath !== null)
                Storage::disk('public')->delete($newImagePath);
    
            throw $exception;
        }

        // delete old image file
        if ($newImagePath !== null && $oldImagePath)
            Storage::disk('public')->delete($oldImagePath);

        return redirect()->route('doctor.profile.show')
            ->with('status', 'Your profile has been updated.');
    }
}
