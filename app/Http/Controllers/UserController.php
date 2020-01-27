<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
// use App\Models\User;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Response;
use Activity;
use Auth;
use Helper;
use DB;

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

    public function list(Request $request)
    {

        $perPage = (int) Helper::handleRequest($request, 'perPage', 10);
        $dateStart = Helper::handleRequest($request, 'dateStart');
        $dateEnd = Helper::handleRequest($request, 'dateEnd');
        $keyWord = Helper::handleRequest($request, 'keyword');

        $responseJson = [];
        $status = 200;
        $users = [];
        $addOnLink = '?perPage=' . $perPage;

        try {
            // DB::enableQueryLog();
            $users = User::selectRaw('users.name, users.email, users.created_at, user_roles.name as role_name')->join('user_roles', function($join){
                $join->on('user_roles.id', '=', 'users.role_id');
            });
            if(strlen($dateStart) > 0) {
                $addOnLink .= '&dateStart=' . $dateStart;
                $dateStart .= ' 00:00:00';
                $users->where('users.created_at', '>=', $dateStart);
            }
            if(strlen($dateEnd) > 0) {
                $addOnLink .= '&dateEnd=' . $dateEnd;
                $dateEnd .= ' 23:59:59';
                $users->where('users.created_at', '<=', $dateEnd);
            }
            if(strlen($keyWord) > 0) {
                $addOnLink .= '&keyword=' . $keyWord;
                $users->where(function($query) use ($keyWord) {
                    $query->orWhere('users.name', 'like', '%'.$keyWord.'%');
                    $query->orWhere('users.email', 'like', '%'.$keyWord.'%');
                    $query->orWhere('user_roles.name', 'like', '%'.$keyWord.'%');
                });
            }
            $users->orderBy('created_at', 'DESC');
            $users = $users->paginate($perPage);
            $users->withPath(url('/api/users' . $addOnLink));
            // dd(DB::getQueryLog());
        } catch (Exception $e) {
            $responseJson = Response::error($e->getMessage());
            $status = 500;
            return response()->json($responseJson, $status);
        }
        Activity::addToLog('Fetch List User');
        $responseJson = Response::success('User fetched', $users);
        return response()->json($responseJson, $status);
    }

}
