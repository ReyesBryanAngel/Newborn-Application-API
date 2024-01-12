<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UUID;

class Feeding extends Model
{
    use HasFactory, UUID;

    protected $table = "feedings";

    protected $fillable = [
        'specimen_form_id',
        'feeding_name',
        'is_selected'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id'];

    public function specimenForm()
    {
        return $this->belongsTo(SpecimenForm::class);
    }
}
