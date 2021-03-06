<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Support\Facades\Hash;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $this->Validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);
        $user = User::where('email', '=', $request->email)->FirstorFail();
        $status = "error";
        $message = "";
        $data = null;
        $code = 401;
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $user->generateToken();
                $status = "Success";
                $message = "Login Sukses";
                $data = $user->toArray();
                $code = 200;
            } else {
                $message = "Login gagal, password salah";
            }
        } else {
            $message = "Login gagal, username salah";
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
        ]);
        $status = "error";
        $message = "";
        $data = null;
        $code = 400;
        if ($validator->fails()) {
        $errors = $validator->errors();
        $message = $errors;
        } else {
        $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'roles' => json_encode(['CUSTOMER']),
        ]);
        if ($user) {
        $user->generateToken();
        $status = "success";
        $message = "register successfully";
        $data = $user->toArray();
        $code = 200;
        } else {
        $message = 'register failed';
        }
        }
        return response()->json([
        'status' => $status,
        'message' => $message,
        'data' => $data
        ], $code);

    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->api_token = null;
            $user->save();
        }
        return response()->json([
            'status'=>'success',
            'message' => 'Logout Berhasil',
            'data' => null
        ], 200);
    }

    public function updateProfile(Request $req, $id)
    {
        $users = Auth::user();

        if($users)
        {
            $user = User::find($id);

            if(!$user){
                return response()->json([
                    'status'=>'failed/error',
                    'message' => 'Data User Tidak ada',
                ], 404);
            }

            $user->name = $req->name;
            $user->email = $req->email;
            $user->password = hash::make($req->password);   

            $user->save();

            return response()->json([
                'status'=>'success',
                'message' => 'Update Berhasil',
                'data' => $user
            ], 200);
        }
    }
}
