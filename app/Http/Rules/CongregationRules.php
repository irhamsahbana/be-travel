<?php

namespace App\Http\Rules;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\Category;

class CongregationRules extends PeopleRules
{
    public function store(Request $request) : array
    {
        $parentRules = parent::store($request);

        $rules = [
            'agent_id' => [
                'required',
                'uuid',
                Rule::exists('people', 'id')->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id)
                        ->where('branch_id', $request->branch_id)
                        ->where('category_id', Category::where('name', 'agent')->where('group_by', 'people')->first()->id ?? null);
                })
            ],
        ];

        return array_merge($parentRules, $rules);
    }

    public function congregationDetail() : array
    {
        $rules = [
            'is_has_meningitis_vaccinated' => ['required', 'boolean'],
            'is_has_family_card' => ['required', 'boolean'],
            'is_has_photo' => ['required', 'boolean'],
            'is_has_mahram' => ['required', 'boolean'],
            'is_airport_handling' => ['required', 'boolean'],
            'is_equipment' => ['required', 'boolean'],
            'is_single_mahram' => ['required', 'boolean'],
            'is_double_mahram' => ['required', 'boolean'],
            'is_pusher_guide' => ['required', 'boolean'],
            'is_special_guide' => ['required', 'boolean'],
            'is_manasik' => ['required', 'boolean'],
            'is_domestic_ticket' => ['required', 'boolean'],
            'blood_type' => ['required', 'string', 'in:A,B,AB,O'],
            'chronic_disease' => ['nullable', 'string', 'max:255'],
            'allergy' => ['nullable', 'string', 'max:255'],
            'passport_number' => ['nullable', 'string', 'max:255'],
            'passport_issued_in' => ['nullable', 'required_with:passport_number', 'string', 'max:255'],
            'passport_issued_at' => ['nullable', 'required_with:passport_number', 'date_format:Y-m-d'],
            'passport_expired_at' => ['nullable', 'required_with:passport_number', 'date_format:Y-m-d'],
            'passport_name' => ['nullable', 'required_with:passport_number', 'string', 'max:255'],
        ];

        return $rules;
    }

    public function service(Request $request): array
    {
        $rules =  [
            'service_id' => [
                'required',
                'uuid',
                Rule::exists('services', 'id')->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id);
                        // ->whereDate('departure_date', '>', date('Y-m-d'));
                }),
            ],
        ];

        return $rules;
    }
}
