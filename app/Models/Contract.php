<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate',
        'start_date',
        'end_date',
    ];

    // Relationship with Accommodation
    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }

    // Relationship with TravelAgent
    public function travelAgent()
    {
        return $this->belongsTo(TravelAgent::class);
    }
}
