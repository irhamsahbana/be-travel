<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Libs\Response;

class AuthController extends Controller
{
    public function attempt(Request $request)
    {
        $fields = [
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password
        ];

        $rules = [
            'username' => 'required_without:email',
            'email' => 'required_without:username',
            'password' => 'required'
        ];

        if ($request->username) unset($fields['email']);
        if ($request->email) unset($fields['username']);

        $validator = Validator::make($fields, $rules);
        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), HttpResponse::HTTP_UNPROCESSABLE_ENTITY);


       if (Auth::attempt($fields)) {
            $user = Auth::user()->with('person', 'person.category')->first();
            $token = $user->createToken('auth_token')->plainTextToken;

            return (new Response)->json([
                'token' => $token,
                'user' => $user,
            ], 'Login success');
        }

        return (new Response)->json(null, 'Invalid login credentials.', HttpResponse::HTTP_UNAUTHORIZED);
    }

    public function me()
    {
        $user = Auth::user()->with('company', 'company.branches', 'branch', 'person', 'person.category')->first();

        return (new Response)->json($user->toArray(), 'Login success');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        $response = new Response();
        return $response->json(null, 'Logout success');
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        $response = new Response();
        return $response->json(null, 'Logout all success');
    }
}
