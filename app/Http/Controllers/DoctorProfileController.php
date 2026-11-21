<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DoctorProfileController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $doctor = $request->user();

        return view('doctor.profile.show', compact('doctor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $doctor = $request->user();

        return view('doctor.profile.edit', compact('doctor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
    }
}
