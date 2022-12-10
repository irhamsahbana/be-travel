<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CongregationDetail extends Model
{
    use HasFactory, HasUuids;

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $guarded = [];

    protected $casts = [
        'is_has_meningitis_vaccinated' => 'boolean',
        'is_has_family_card' => 'boolean',
        'is_has_photo' => 'boolean',
        'is_has_mahram' => 'boolean',
        'is_airport_handling' => 'boolean',
        'is_equipment' => 'boolean',
        'is_single_mahram' => 'boolean',
        'is_double_mahram' => 'boolean',
        'is_pusher_guide' => 'boolean',
        'is_special_guide' => 'boolean',
        'is_manasik' => 'boolean',
        'is_domestic_ticket' => 'boolean',
        'is_has_family_card' => 'boolean',
    ];

    public function congregation()
    {
        return $this->belongsTo(Person::class, 'congregation_id');
    }
}
