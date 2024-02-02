<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use Illuminate\Http\Request;

class AccommodationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accommodations = Accommodation::all();
        return view('accommodations.index', compact('accommodations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('accommodations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'standard_rack_rate' => 'required|numeric',
            'location' => 'string|nullable',
        ]);

        Accommodation::create($request->all());

        return redirect()->route('accommodations.index')
            ->with('success', 'Accommodation created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        return view('accommodations.show', compact('accommodation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        return view('accommodations.edit', compact('accommodation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'standard_rack_rate' => 'required|numeric',
            'location' => 'string|nullable',
        ]);

        $accommodation = Accommodation::findOrFail($id);
        $accommodation->update($request->all());

        return redirect()->route('accommodations.index')
            ->with('success', 'Accommodation updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $accommodation = Accommodation::findOrFail($id);
        $accommodation->delete();

        return redirect()->route('accommodations.index')
            ->with('success', 'Accommodation deleted successfully');
    }
}
