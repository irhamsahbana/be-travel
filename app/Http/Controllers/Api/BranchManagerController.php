<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Libs\Response;
use App\Mail\RegisteredMail;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BranchManagerController extends Controller
{
    public function index()
    {
        //
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
        ];

        $validator = Validator::make($fields, $rules);
        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        $category = Category::where('group_by', 'people')
            ->where('name', 'branch-manager')
            ->first();

        try {
            DB::beginTransaction();
            $person = Person::create([
                'category_id' => $category->id,
                'name' => $fields['name'],
                'email' => $fields['email'],
                'phone' => $fields['phone'],
            ]);

            $user = User::create([
                'person_id' => $person->id,
                'company_id' => $fields['company_id'],
                'branch_id' => $fields['branch_id'],
                'password' => bcrypt($password = Str::random(8)),
            ]);
        } catch (\Throwable $th) {
            if (DB::transactionLevel() > 0) DB::rollBack();

            throw $th;
        }

        Mail::to($person->email)->send(new RegisteredMail($person, $password));
        return (new Response)->json($user->load('person')->toArray(), 'branch manager created successfully');
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
