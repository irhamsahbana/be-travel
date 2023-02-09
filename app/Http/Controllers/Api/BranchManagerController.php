<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use App\Libs\Response;
use App\Mail\RegisteredMail;
use Illuminate\Support\Facades\Mail;

use App\Models\Category;
use App\Models\User;
use App\Models\Person;

class BranchManagerController extends Controller
{
    public function index()
    {
        $user = $this->getUser();
        $userCategory = $user?->person?->category?->name;

        if ($userCategory !== 'director')
            return (new Response)->json(null, self::NOT_AUTHORIZED_MESSAGE, 403);

        $data = Person::with(['branch'])->whereHas('category', function ($query) {
            $query->where('group_by', 'people')
                ->where('name', 'branch-manager');
            })->where('company_id', $user->company_id)
            ->get()->toArray();

        return (new Response)->json($data, 'branch managers fetched successfully');
    }

    public function store(Request $request)
    {
        $user = $this->getUser();
        $userCategory = $user?->person?->category?->name;

        if ($userCategory !== 'director')
            return (new Response)->json(null, self::NOT_AUTHORIZED_MESSAGE, 403);

        $fields = [
            'company_id' => $user->company_id,
            'branch_id' => $request->branch_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'password_confirmation' => $request->password_confirmation,
        ];

        $rules = [
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
            'branch_id' => [
                'required', 'uuid',
                Rule::exists('branches', 'id')->where(function ($query) use ($fields) {
                    $query->where('company_id', $fields['company_id']);
                })
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:people'],
            'phone' => ['required', 'string', 'max:255', 'unique:people'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        $validator = Validator::make($fields, $rules);
        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        $category = Category::where('group_by', 'people')
            ->where('name', 'branch-manager')
            ->first();

        try {
            DB::beginTransaction();
            $person = Person::create([
                'company_id' => $fields['company_id'],
                'branch_id' => $fields['branch_id'],
                'category_id' => $category->id,
                'name' => $fields['name'],
                'email' => $fields['email'],
                'phone' => $fields['phone'],
            ]);

            $user = User::create([
                'company_id' => $fields['company_id'],
                'branch_id' => $fields['branch_id'],
                'person_id' => $person->id,
                'email' => $fields['email'],
                'password' => bcrypt($fields['password']),
            ]);

            DB::commit();
            // Mail::to($person->email)->send(new RegisteredMail($person, $fields['password']));
            return (new Response)->json($person->load('branch')->toArray(), 'branch manager created successfully');
        } catch (\Throwable $th) {
            if (DB::transactionLevel() > 0) DB::rollBack();

            throw $th;
        }
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $user = $this->getUser();
        $userCategory = $user?->person?->category?->name;

        if ($userCategory !== 'director')
            return (new Response)->json(null, self::NOT_AUTHORIZED_MESSAGE, 403);

        $fields = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'password_confirmation' => $request->password_confirmation,
        ];

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:people,email,' . $id],
            'phone' => ['required', 'string', 'max:255', 'unique:people,phone,' . $id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        $validator = Validator::make($fields, $rules);
        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        $person = Person::with(['branch'])
            ->where('id', $id)
            ->whereHas('category', function ($query) {
                $query->where('group_by', 'people')
                ->where('name', 'branch-manager');
            })->where('company_id', $user->company_id)
            ->first();

        if (!$person) return (new Response)->json(null, 'branch manager not found', 404);

        try {
            DB::beginTransaction();
            $person->update([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'phone' => $fields['phone'],
            ]);

            if ($fields['password']) {
                $person->user?->update([
                    'password' => bcrypt($fields['password']),
                ]);
            }

            DB::commit();
            $data = $person->refresh()->load('branch')->toArray();
            return (new Response)->json($data, 'branch manager updated successfully');
        } catch (\Throwable $th) {
            if (DB::transactionLevel() > 0) DB::rollBack();

            throw $th;
        }
    }

    public function destroy($id)
    {
        $user = $this->getUser();
        $userCategory = $user?->person?->category?->name;

        if ($userCategory !== 'director')
            return (new Response)->json(null, self::NOT_AUTHORIZED_MESSAGE, 403);

        $person = Person::with(['branch'])
            ->where('id', $id)
            ->whereHas('category', function ($query) {
                $query->where('group_by', 'people')
                ->where('name', 'branch-manager');
            })->where('company_id', $user->company_id)
            ->first();

        if (!$person) return (new Response)->json(null, 'branch manager not found', 404);

        $data = $person->toArray();

        try {
            DB::beginTransaction();
            $person->user?->delete();
            $person->delete();

            DB::commit();
            return (new Response)->json($data, 'branch manager deleted successfully');
        } catch (\Throwable $th) {
            if (DB::transactionLevel() > 0) DB::rollBack();

            throw $th;
        }
    }
}
