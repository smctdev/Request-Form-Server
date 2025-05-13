<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\User;

class ResetPasswordController extends Controller
{
    public function reset(Request $request)
    {
      
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);


       $updatePassword = DB::table('password_reset_tokens')
       ->where([
            "email" =>$request->email,
            "token" =>$request->token,
       ])->first();

       if(!$updatePassword){
        return response()->json(['message' => 'Invalid.'], 400);
       }

       User::where("email",$request->email)
       ->update(["password" => Hash::make($request->password)]);

       DB::table(table:"password_reset_tokens")
       ->where(["email"=>$request->email])
       ->delete();

       return response()->json(['message' => 'Password reset successfully.'], 200);

       
    }
}
