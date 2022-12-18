<?php

namespace App\Http\Rules;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PeopleRules
{
    public function store(Request $request) : array
    {
        return [
            // people table
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
            'branch_id' => [
                'required',
                'uuid',
                Rule::exists('branches', 'id')->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id);
                })
            ],
            'ref_no' => ['required', 'string', 'max:255', 'unique:people,id,'. $request->id],
            'name' => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
            'place_of_birth' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date_format:Y-m-d'],
            'sex' => ['required', 'in:male,female'],
            'national_id' => ['required', 'string', 'max:30', 'unique:people,national_id,'. $request->id],
            'address' => ['required', 'string', 'max:255'],
            'city_id' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where('group_by', 'cities');
                })
            ],
            'nationality_id' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where('group_by', 'nationalities');
                })
            ],
            'phone' => ['required', 'string', 'max:15', 'unique:people,phone,'. $request->id,
                'regex:/^62[0-9]{6,15}$/' // the regex is for Indonesian phone number (62 is the country code, 6-11 is the phone number)
            ],
            'wa' => ['required', 'string', 'max:15', 'unique:people,wa,'. $request->id,
                'regex:/^62[0-9]{6,15}$/' // the regex is for Indonesian phone number (62 is the country code, 6-11 is the phone number)
            ],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:people,email,'. $request->id],
            'education_id' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where('group_by', 'educations');
                })
            ],
            'profession' => ['required', 'string', 'max:255'],
            'marital_status_id' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where('group_by', 'marital_statuses');
                })
            ],
            'account_name' => ['required', 'string', 'max:255'],
            'bank_id' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where('group_by', 'banks');
                })
            ],
            'account_number' => ['required', 'string', 'max:25'],
            'emergency_name' => ['required', 'string', 'max:255'],
            'emergency_address' => ['required', 'string', 'max:255'],
            'emergency_home_phone' => ['required', 'string', 'max:15'],
            'emergency_phone' => ['required', 'string', 'max:15'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function user() : array
    {
        return [
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
        ];
    }
}
