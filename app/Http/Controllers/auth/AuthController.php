<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __Construct()
    {
        $this->middleware('jwt.verify')->except('login');
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users',
            'password' => 'required|string|min:8'
        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 400);
        $user = User::where('email', $request->email)->first();
        if ($user->is_activated) {
            if (Hash::check($request->pasword, $user->password)) {
                $token = JWTAuth::fromUser($user);
                return response()->json([
                    'message' => 'success',
                    'token' => $token
                ], 200);
            }
            return response()->json([
                'message' => 'password is not correct !',
            ], 401);
        }
        $user->delete();
        return response()->json([
            'message' => 'you need to register first !'
        ], 401); ////
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'message' => 'success'
        ], 200);
    }


    public function user_profile()
    {
        $user = JWTAuth::user();
        if (isset($user))
            return response()->json([
                'user' => $user
            ], 200);
        return response()->json([
            'message' => 'user not found'
        ], 404);
    }

    public function update_user(Request $request, $id)
    {
        $validator = Validator()->make($request->all(), [
            'name' => 'required',
            //'email' => 'required|email|unique:users,email,' . $id,
            'email' => 'required|email|exists:users',
            'password' => 'confirmed',
            'roles' => 'required'
        ]);
        if ($validator->fails())
            return response()->json($validator->errors(), 400);
        $data=array_merge(
            $validator->validated(),
            ['password'=>bcrypt($request->password)]
        );
        DB::table('users')->find($id)->update($data);
        DB::table('model_has_roles')
            ->where('model_id', $id)
            ->delete();
        $user=User::find($id);
        $user->assignRole($request->input('roles'));
        return response()->json([
            'message'=>'user updated successfully',
            'user'=>$user
        ],200);
    }


    public function delete_acount(){

        $id=JWTAuth::user()->id;
        User::delete($id);
        return response()->json([
            'message'=>'acount deleted successfully'
        ],200);
    }
}
