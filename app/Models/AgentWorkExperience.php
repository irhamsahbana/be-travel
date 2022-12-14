<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AgentWorkExperience extends Model
{
    use HasFactory, HasUuids;

    protected $guarded  = [];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
