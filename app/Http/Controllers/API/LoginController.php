<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{

    public function login(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'email'    => 'required',
            'password' => 'required',
        ]);

        // If validation fails, return errors
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation Error.',
                'errors'  => $validator->errors(),
            ]);
        }

        if ($request->user()) {
            abort(226, 'You are already logged in.');
        }

        $user = User::where('email', $request->email)
            ->orWhere('userName', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status'        =>          false,
                'message'       =>          'We couldn\'t find an account with that email.',
            ], 403);
        }

        if (!$user || $user->email_verified_at === null) {
            return response()->json([
                'status'        =>          false,
                'message'       =>          'Your email has not been verified yet. Please contact the administrator.',
            ], 403);
        }

        if (!Auth::guard('web')->attempt([
            "email"     => filter_var($request->email, FILTER_VALIDATE_EMAIL) ? $request->email : $user->email,
            'password'  => $request->password
        ])) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid Credentials.',
            ], 400);
        }

        $userRole = Auth::user()->role;
        // Return user data along with the token and expiration time

        $request->session()->regenerate();

        return response()->json([
            'status'           => true,
            'message'          => 'Login successful. Redirecting you to Dashboard.',
            "role"             => $userRole
        ], 200);
    }
}
