<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

use App\User;

class UserController extends Controller
{
    //* Disable Auth middleware for user creation and logging in
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'create']]);
    }

    //* Create a new account
    public function create(Request $req)
    {   
        // Basic validation
        $validator = Validator::make($req->all(), [
        'name' => 'required|alpha_num|max:25|unique:users',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8',
        'confirm_password' => 'required|same:password'
        ]);

        if($validator->fails())
        {
            return response()->json(['errors' => $validator->errors()->all()], 400);
        }

        // Create user object
        $user = new User;

        $user->name = $req->name;
        $user->email = $req->email;
        $user->password = Hash::make($req->password);

        $user->save();

        // Everything is fine
        return json_encode(['status' => 'ok'], 200);
    }

    //* Sign in to existing account
    public function login(Request $req)
    {
        // Basic validation
        $validator = Validator::make($req->all(), [
        'name' => 'required|alpha_num',
        'password' => 'required'
        ]);

        if($validator->fails())
        {
        return response()->json(['errors' => $validator->errors()->all()], 400);
        }

        // Get only credentials from the request
        $credentials = $req->only('name', 'password');

        // If the credentials are invalid, refuse to log in!
        if (!$token = Auth::attempt($credentials))
        {
            return response()->json(['error' => 'invalid_credentials'], 401);
        }

        // Ok, send the JWT token!
        return $this->respondWithToken($token);
    }

    //* Get currently logged in user profile
    public function current()
    {
        return response()->json(Auth::user());
    }

    //* Logout current user
    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    //* Refresh the access token
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /** 
     * * Create response with JWT Token
     * @param token The token to respond with
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }
}