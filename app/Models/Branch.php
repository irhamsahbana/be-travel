<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Branch extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function agents()
    {
        return $this->hasMany(Person::class)->whereHas('category', function ($query) {
            $query->where('name', 'agent');
        });
    }
}
