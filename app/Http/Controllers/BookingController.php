<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\Http\Request;
use App\Models\Accommodation;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index()
    {
        $accommodations = Accommodation::all();
        $contracts = Contract::with('accommodation')->where('travel_agent_id', Auth::id())->get();

        return view('bookings.index', compact('accommodations', 'contracts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'accommodation_id' => 'required|exists:accommodations,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);



        return redirect()->route('bookings.index')->with('success', 'Booking successful!');
    }
}
