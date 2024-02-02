<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;

class ContractApiController extends Controller
{
    public function index()
    {
        return Contract::all();
    }

    public function show(Contract $contract)
    {
        return $contract;
    }

    public function store(Request $request)
    {
        $request->validate([
            'accommodation_id' => 'required|exists:accommodations,id',
            'travel_agent_id' => 'required|exists:travel_agents,id',
            'contract_rates' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        return Contract::create($request->all());
    }

    public function update(Request $request, Contract $contract)
    {
        $request->validate([
            'accommodation_id' => 'required|exists:accommodations,id',
            'travel_agent_id' => 'required|exists:travel_agents,id',
            'contract_rates' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $contract->update($request->all());

        return $contract;
    }

    public function destroy(Contract $contract)
    {
        $contract->delete();

        return response()->json(null, 204);
    }
}

