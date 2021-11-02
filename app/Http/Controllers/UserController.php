<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    //
    public function register(Request $request) {
        try {
            $request->validate([
                'email' => ['required','email','unique:users'],
                'name' => ['required'],
                'gender' => ['string', 'max:1'],
                'password' => ['required'],
            ]);
    
            $user = new User();
            $user->name = $request->input('name');
            $user->is_login = 0;
            $user->email = $request->input('email');
            $user->encrypted_password = Hash::make($request->input('password'));
            if ($request->gender != "M" && $request->gender != "F") {
                $this->returnJsonErrorDataNotValid("Gender must be 'M' of 'F'");
            }
            $user->gender = $request->input('gender');
            $user->save();
            // dd($user);
            return response()->json([
                'code' => 1,
                'message' => 'registered'
            ],201);
        }catch(Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e,
            ], 403);
        }
    }
    public function login(Request $request) {
        try {
            $request->validate([
                'email' => 'required',
                'password' => 'required'
            ]);
    
            $email = $request->input('email');
            $password = $request->input('password');
            $data = User::where('email', $email)->first();
    
            if(Hash::check($password, $data->encrypted_password)) {
                $data->auth_token = Str::random(32);
                $data->save();

                return response()->json([
                    'code' => 1,
                    'message' => 'Login Successfully',
                    'data' => $data
                ]);
            }
            // dd($user);
    
            
        }catch(Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => $e,
            ], 403);
        }
    }

    public function logout(Request $request) {
        try {
            $request->validate([
                'token' => ['required']
            ]);
    
            $data = DB::table('users')
            ->where('auth_token', $request->token)
            ->update([
                'is_login' => FALSE,
                'updated_at' => date("Y-m-d H:i:s"),
                'auth_token' => NULL
            ]);
            // $data->save();
            return response()->json([
                'code' => 1,
                'message' => 'Logout Successfully'
            ]);
        } catch(Exception $e) {
            return response()->json([
                'code' => 400,
                'message' => 'logout failed',
            ], 400);
        }
    }
}
