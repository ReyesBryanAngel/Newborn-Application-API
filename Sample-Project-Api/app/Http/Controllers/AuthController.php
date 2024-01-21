<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Mail\SendOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Http\Requests\AuthControllerRequest;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(AuthControllerRequest $request) {
        $authRequest = $request->validated();
        $user = User::where('email', $authRequest['email'])->first();

        if ($user && $user->is_verified) {
            return response()->json([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'status' => 'failed',
                'message' => 'Account is already taken.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        if (!$user) {
            $user = User::create([
                'name' => $authRequest['name'],
                'email' => $authRequest['email'],
                'last_name' => $authRequest['last_name'],
                'password' => Hash::make($authRequest['password']),
                'otp' =>(new User())->generateOtp(),
            ]);
        } else {
            $user->update([
                'otp' => $user->generateOtp(),
            ]);
        }

        Mail::to($authRequest['email'])->send(new SendOtpMail($user->otp));

        return response()->json([
            'id' => $user?->id,
            'status' => 'success',
            'message' => 'Check your email for OTP verification.'
        ]);
    }

    public function resendOtp($id)
    {
        $userVerified = User::find($id);

        if (!$userVerified) {
            return response()->json([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'status' => 'failed',
                'message' => 'Pending account does not exists'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);  
        }

        $userVerified->update([
            'otp' => $userVerified->generateOtp()
        ]);

        Mail::to($userVerified->email)->send(new SendOtpMail($userVerified->otp));

        return response()->json([
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Check your email for OTP verification.'
        ], Response::HTTP_OK);
    }

    public function verify(Request $request, $id)
    {
        $userVerified = User::find($id);

        if (!$userVerified) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'error',
                'message' => 'ID is not associated with the pending account that you are trying to register',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (Carbon::now()->diffInSeconds($userVerified->updated_at) > 60) {
            return response()->json([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'status' => 'failed',
                'message' => 'OTP expires. Please resend another one.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($userVerified->otp !== $request->otp) {
            return response()->json([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'status' => 'error',
                'message' => 'OTP is invalid.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $userVerified->update([
            'is_verified' => true,
            'email_verified_at' => Carbon::now()
        ]);

        return response()->json([
            'code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Email verification successful. You can now log in.',
        ], Response::HTTP_CREATED);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $request->email)->first();

        $token = Auth::attempt($credentials);
        if (!$token || !$user->is_verified) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid User Credentials',
            ], RESPONSE::HTTP_UNAUTHORIZED);
        }

        return $this->createNewToken($token);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function profile()
    {
        return response()->json([
            'user' => Auth::user(),
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
    
    public function createNewToken($token) 
    {
            $user = auth()->user();
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->setTTL(1)->getTTL() * 60,
                'user' => [
                    'id' => $user->id,
                    'last_name' => $user->last_name,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user == null) {
            return response()->json([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'status' => 'failed',
                'message' => 'Email does not exist'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);  
        } 

        $token = $user->generatePasswordResetToken();

        $existingToken = PasswordResetToken::where('email', $request->email)->first();

        if ($existingToken) {
            $existingToken->update([
                'token' => $token,
                'updated_at' => Carbon::now(),
                'used' => false
            ]);
        } else {
            PasswordResetToken::create([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);
        }

        Mail::to($request->email)->send(new ResetPasswordMail($token, $existingToken->email, $user->name));

        return response()->json([
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Reset password request has been sent to your email.'
        ], Response::HTTP_OK);   
    }

    public function validateToken($token)
    {
        $passwordReset = PasswordResetToken::where('token', $token)->first();
        $isTokenExpired = Carbon::now()->diffInMinutes($passwordReset->updated_at) > 60;

        if (!$token) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'failed',
                'message' => 'token is invalid'
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($isTokenExpired) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'failed',
                'message' => 'token is expired'
            ], Response::HTTP_BAD_REQUEST);
        }
        

        return response()->json(['token' => $passwordReset->token], Response::HTTP_OK);
    }

    public function changePassword(Request $request)
    {
        $password = $request->only(['password', 'password_confirmation', 'token']);

        $passwordReset = PasswordResetToken::where('token', $password['token'])->first();

        $response = null;
        $statusCode = Response::HTTP_OK;

        switch (true) {
            case $request->password !== $request->password_confirmation:
                $response = 'Password did not match';
                $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
                break;
            case !$passwordReset:
                $response = 'Invalid token';
                $statusCode = Response::HTTP_BAD_REQUEST;
                break;
            case Carbon::now()->diffInMinutes($passwordReset->updated_at) > 60:
                $response = 'Token is expired';
                $statusCode = Response::HTTP_BAD_REQUEST;
                break;
            case $passwordReset->used == true:
                $response = 'Token is used already. Please make another forgot password request.';
                $statusCode = Response::HTTP_BAD_REQUEST;
                break;
            default:
                $user = User::where('email', $passwordReset->email)->first();
                $user->update([
                    'password' => Hash::make($request->password),
                ]);
                $passwordReset->update([
                    'used' => true
                ]);
                $response = 'password has been changed successfully.';
                $statusCode = Response::HTTP_OK;
        }
   
        return response()->json($response, $statusCode);
    }
}