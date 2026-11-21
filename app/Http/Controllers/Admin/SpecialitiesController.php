<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Specialities;
use Illuminate\Http\Request;

class SpecialitiesController extends Controller
{
     public function index()
    {
        $specialities=Specialities::all();
        return view ('admin.specialities.index',compact('specialities'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.specialities.create');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $validate=$request->validate([
        'name'=>'required|string|max:255|unique:specialities,name',
        'description'=>'nullable|string'
       ]);

       Specialities::create($validate);
       return redirect()->route('admin.specialities.index')->with('success','Speciality added successfully');
    }




    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $speciality=Specialities::findOrFail($id);
        return view('admin.specialities.edit',compact('speciality'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $speciality=Specialities::findOrFail($id);
        $validate=$request->validate([
            'name'=>'required|string|max:255|unique:specialities,name,' .$id,
              'description' => 'nullable|string',
              
          
        ]);
     
            
              $speciality->update($validate);
                return redirect()->route('admin.specialities.index')
        ->with('success', 'Speciality updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $speciality=Specialities::findOrFail($id);
        $speciality->delete();
        return redirect()->route('admin.specialities.index')->with('success','Speciality deleted successfully');
    }
}


