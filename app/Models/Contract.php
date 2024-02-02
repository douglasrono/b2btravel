<?php

// app\Models\Contract.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'rate', 'start_date', 'end_date', 'accommodation_id', 'travel_agent_id',
    ];

    // Relationship with Accommodation
    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }

    // Relationship with Travel Agent
    public function travelAgent()
    {
        return $this->belongsTo(User::class, 'travel_agent_id');
    }
}

