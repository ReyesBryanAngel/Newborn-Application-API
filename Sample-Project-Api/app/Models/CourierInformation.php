<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UUID;

class CourierInformation extends Model
{
    use HasFactory, UUID;

    protected $table = "courier_informations";

    protected $fillable = [
        'user_id',
        'courier',
        'tracking_number',
        'date_of_pickup',
        'notes',
        'result'
    ];

    const COURIER_OPTIONS = [
        'GRAB',
        '2GO',
        'NINJAVAN',
        'LBC'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
