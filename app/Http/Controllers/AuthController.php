<?php

namespace App\Http\Controllers;

use App\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request) {
        $validate = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);

        if($validate->fails()) return response()->json(['message' => 'invalid login'], 401);

        $user = User::where('username', $request->username);
        $users = $user->get();
        $check = $user->count();

        if($check>0) {
            foreach($users as $user) {
                if(Hash::check($request->password, $user->password)) {
                    $token = md5($user->username);
                    if($user->token == "") $user->update(['token' => $token]);
                    return response()->json([
                        'token' => $token,
                        'role' => $user->role,
                        'username' => $user->username
                    ]);
                }
                return response()->json(['message' => 'invalid login'], 401);
            }
        }
    }

    public function logout(Request $request) {
        $user = User::where('token', $request->token)->first();

        if(!$user) return response()->json(['message' => 'unauthorized user'], 401);

        $user->update([
            'token' => ''
        ]);
        return response()->json(['message' => 'logout success'], 200);
    }
}
