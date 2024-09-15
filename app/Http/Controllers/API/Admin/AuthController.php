<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }

        // Find user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists and if the password matches
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Login failed'
            ], 401);
        }

        // Return successful login response
        return response()->json([
            'message' => 'Login success!',
            'data' => $user,
            'token' => $user->createToken('authToken')->accessToken
        ], 200);
    }

    public function logout(Request $request)
    {
        try {
            // Revoke the user's current token
            $request->user()->token()->revoke();

            return response()->json([
                'message' => 'Logout success'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to logout',
                'error_log' => $e->getMessage()
            ], 401);
        }
    }
}