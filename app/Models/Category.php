<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

// use App\Models\Helper\Uuid;

class Category extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function packetTypes($companyId = null)
    {
        if ($companyId == null) return $this->where('group_by', 'packet_types')->whereNull('company_id');
        return $this->where('group_by', 'packet_types')->where('company_id', $companyId);
    }

    public function people()
    {
        return $this->where('group_by', 'people');
    }

    public function maritalStatuses()
    {
        return $this->where('group_by', 'marital_statuses');
    }

    public function nationalities()
    {
        return $this->where('group_by', 'nationalities');
    }

    public function cities()
    {
        return $this->where('group_by', 'cities');
    }

    public function banks()
    {
        return $this->where('group_by', 'banks');
    }

    public function educations()
    {
        return $this->where('group_by', 'educations')->orderBy('notes', 'asc');
    }

    public function paymentMethods()
    {
        return $this->where('group_by', 'payment_methods');
    }
}
