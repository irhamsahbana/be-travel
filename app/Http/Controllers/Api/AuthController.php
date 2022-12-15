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
            'identifier' => $request->identifier,
            'password' => $request->password
        ];

        $rules = [
            'identifier' => 'required',
            'password' => 'required'
        ];

        $validator = Validator::make($fields, $rules);
        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        $identifier = $fields['identifier'];
        $loginType = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $fields = [
            $loginType => $identifier,
            'password' => $fields['password']
        ];

        if (Auth::attempt($fields)) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return (new Response)->json([
                'token' => $token,
                'user' => $user,
            ], 'Login success');
        }

        return (new Response)->json(null, 'Invalid login credentials.', 401);
    }

    public function me()
    {
        $user = $this->getUser();
        if ($user->person->category->name == 'director' || $user->person->category->name == 'branch-manager') {
            $user = Auth::user()->load([
                'company.accounts',
                'company.branches',
                'person',
                'person.category'
            ]);
        } else if ($user->person->category->name == 'agent') {
            $user = Auth::user()->load([
                'company.accounts',
                'company.branches',
                'person',
                'person.category',
                'person.agentWorkExperiences'
            ]);
        }

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
