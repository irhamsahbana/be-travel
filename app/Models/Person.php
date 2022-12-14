<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Person extends Model
{
    use HasFactory, HasUuids;

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $guarded = [];

    protected $casts = [
        'agent_work_experiences' => 'array',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'person_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function city()
    {
        return $this->belongsTo(Category::class, 'city_id');
    }

    public function nationality()
    {
        return $this->belongsTo(Category::class, 'nationality_id');
    }

    public function file()
    {
        return $this->morphOne(File::class, 'fileable')->latest();
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    // for agent only
    public function agentWorkExperiences()
    {
        return $this->hasMany(AgentWorkExperience::class, 'person_id');
    }

    public function agentInvoices()
    {
        return $this->hasMany(Invoice::class, 'agent_id');
    }

    public function registeredCongregations()
    {
        return $this->hasMany(Person::class, 'agent_id');
    }

    // for congregation only
    public function agent()
    {
        return $this->belongsTo(Person::class, 'agent_id');
    }

    public function congregationInvoices()
    {
        return $this->hasMany(Invoice::class, 'congregation_id');
    }

    public function congregationDetail()
    {
        return $this->hasOne(CongregationDetail::class, 'person_id');
    }
}
