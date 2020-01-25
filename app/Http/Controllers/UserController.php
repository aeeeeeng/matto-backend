<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Response;
use Activity;
use Auth;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $this->validate($credentials, User::ruleLogin());

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                Activity::addToLog('Fail Login, ' . 'invalid_credentials');
                $responseJson = Response::error('invalid_credentials');
                return response()->json($responseJson, 400);
            }
        } catch (Exception $e) {
            Activity::addToLog('Fail Login, ' . $e->getMessage());
            $responseJson = Response::error($e->getMessage());
            return response()->json($responseJson, 500);
        } catch (JWTException $ex) {
            Activity::addToLog('Fail Login, ' . $ex->getMessage());
            $responseJson = Response::error($ex->getMessage());
            return response()->json($responseJson, 500);
        }
        Activity::addToLog('Login');
        $user = Auth::user();
        $isAdmin = $user->isAdmin();
        $user->isAdmin = $isAdmin;
        $responseJson = Response::success('Success login', $user );
        return response()->json($responseJson)->withHeaders([
            'Authorization' => $token
        ]);
    }

    public function register(Request $request)
    {

        Activity::addToLog('Register new user');

        $validator = Validator::make($request->all(), array(
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed'
        ));

        if($validator->fails()){
            $responseJson = Response::error($validator->errors());
            return response()->json($responseJson, 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);
        $responseJson = Response::success('Register Success', compact('user','token'));
        return response()->json($responseJson,200);
    }

    public function refreshAuth()
    {
        $responseJson = Response::success('success authenticated');
        return response()->json($responseJson);
    }

    public function logout()
    {
        $responseJson = [];
        $status = 200;
        try {
            auth()->logout(true);
        } catch(Exception $e) {
            $responseJson = Response::error($e->getMessage());
            $status = 500;
        }
        $responseJson = Response::success('Logout');
        Activity::addToLog('Logout');
        return response()->json($responseJson, $status);

    }

}
