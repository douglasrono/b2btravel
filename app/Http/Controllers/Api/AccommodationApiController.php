<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use Illuminate\Http\Request;

class AccommodationApiController extends Controller
{
    public function index()
    {
        return Accommodation::all();
    }

    public function show(Accommodation $accommodation)
    {
        return $accommodation;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'standard_rack_rate' => 'required|numeric',
        ]);

        return Accommodation::create($request->all());
    }

    public function update(Request $request, Accommodation $accommodation)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'standard_rack_rate' => 'required|numeric',
        ]);

        $accommodation->update($request->all());

        return $accommodation;
    }

    public function destroy(Accommodation $accommodation)
    {
        $accommodation->delete();

        return response()->json(null, 204);
    }
}

