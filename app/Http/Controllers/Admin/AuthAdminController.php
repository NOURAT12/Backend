<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\VerifyMail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('login', 'verifyOtp', 'sendOtp');
    }
    //////// login
    public function login(Request $request)
    {
        $validate = Validator::make(
            $request->only('password', 'username'),
            [
                'password' => 'required|min:8',
                'username' => 'required|string|exists:users,username|max:255',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        if (!auth()->validate($request->only('password', 'username'))) {
            return $this->failResponse('البيانات المدخلة غير صحيحة');
        }
        $user = User::where('username', $request->username)->first();
        $role = $user->roles[0]['name'];
        $credate = $request->only('password', 'username');
        $token=auth()->attempt($credate);
        Arr::forget($user, 'roles');
        $data=[
            'user'=>$user,
            'token'=>$token,
            'role'=>$role,
        ];
        return $this->createResponse($data);
    }

    /////////// send Otp
    public function sendOtp(Request $request)
    {
        $validate = Validator::make(
            $request->only('username'),
            [
                'username' => 'required|string|exists:users,username|max:255',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $user = User::where('username', $request->username)->first();
        $otp = rand(100000, 999999);
        if ($user) {
            $details = ['otp' => $otp, 'name' => $user->name];

            try {
                Mail::to($request->username)->send(new VerifyMail($details));
            } catch (Exception $e) {
                return $this->serverResponse();
            }
            $user->update(['otp' => $otp]);
            return $this->successResponse('sent successfully');
        }
    }

    /////////// verifyOtp
    public function verifyOtp(Request $request)
    {
        $validate = Validator::make(
            $request->only('otp', 'username'),
            [
                'otp' => 'required|integer',
                'username' => 'required|string|exists:users,username|max:255',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $user = User::where('username', $request->username)->where('otp', $request->otp)->first();
        if ($user) {
            if ($user->updated_at < now()->subMinutes(6)) {
                $user->otp = null;
                $user->save();
                return $this->failResponse("Request Timeout");
            }
            $user->otp = null;
            $user->markEmailAsVerified();
            $user->save();
            $token = JWTAuth::fromUser($user);
            return $this->loginResponse($user, $token, "verified successfully");
        } else {
            return $this->t403Response("unverified");
        }
    }

    /////////// resetPassword
    public function resetPassword(Request $request)
    {
        $validate = Validator::make(
            $request->only('password'),
            [
                'password' => 'required|min:8',
            ]
        );
        if ($validate->fails()) {
            return $this->badResponse($validate);
        }
        $user = User::find(auth()->user()->id)->first();
        $user->update([
            'password' => Hash::make($request->password)
        ]);
        return $this->successResponse('reset successfully');
    }

    /// logout
    public function logout()
    {
        auth()->logout();
        return $this->successResponse('logout successfully');
    }
}
