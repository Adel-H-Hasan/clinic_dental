<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::whereIn('role', ['patient', 'doctor'])->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::whereIn('role', ['patient', 'doctor'])->findOrFail ($id);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::whereIn('role', ['patient', 'doctor'])->findOrFail ($id);
        
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::whereIn('role', ['patient', 'doctor'])->findOrFail ($id);

        $data = $request->validate([
            'is_active' => ['required', 'boolean']
        ]);

        // copies the submitted status onto the selected user in memory.
        $user->is_active = $data['is_active'];
        // writes that change to the database
        $user->save ();

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully');
    }
}
