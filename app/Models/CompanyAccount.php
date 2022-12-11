<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CompanyAccount extends Model
{
    use HasFactory, HasUuids;

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function bank()
    {
        return $this->belongsTo(Category::class, 'bank_id');
    }
}
