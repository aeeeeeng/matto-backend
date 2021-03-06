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


class UserController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
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
        $responseJson = Response::success(['token' => $token]);
        return response()->json($responseJson);
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

    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch(Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        return response()->json(compact('user'));
    }

}
