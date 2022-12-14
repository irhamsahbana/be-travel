<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Company extends Model
{
    use HasFactory, HasUuids;

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $guarded = [];

    public function branches()
    {
        return $this->hasMany(Branch::class, 'company_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'company_id');
    }

    public function accounts()
    {
        return $this->hasMany(CompanyAccount::class, 'company_id');
    }

    public function people()
    {
        return $this->hasMany(Person::class, 'company_id');
    }
}
