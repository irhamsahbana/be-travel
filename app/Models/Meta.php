<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Meta extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'table_name',
        'fk_id',
        'key',
        'value',
    ];
}
