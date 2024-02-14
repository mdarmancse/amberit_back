<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Logger;
use App\Models\Role;
use App\Models\SecUserAccessTbl;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Helpers\ApiResponse;
use Symfony\Component\HttpFoundation\Cookie;

class AuthController extends Controller
{


    public function login(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
                'is_remember' => 'boolean',
            ]);

            if ($validateUser->fails()) {
                return ApiResponse::error(422, 'Validation error', $validateUser->errors());
            }

            $credentials = $request->only(['email', 'password']);

            if (!Auth::attempt($credentials)) {
                return ApiResponse::error(500, 'Authentication failed', 'Email & Password do not match with our records.');
            }

            $user = User::where('email', $request->email)->first();

            if ($user) {
                $user->update(['last_activity' => now()]);
                $token = $user->createToken('auth_token', ['*']);
                $cookie = cookie('auth_token', $token->plainTextToken, 60); // 1 hour expiration

                $rolesPivot = $user->roles->first() ? $user->roles->first()->pivot : [];


                if ($user){
                    Logger::createLog( "{$user->name} login to CMS",'read','user',$user);
                }

                $userAccess = SecUserAccessTbl::leftJoin('sec_role_permissions', 'sec_user_access_tbls.fk_role_id', '=', 'sec_role_permissions.role_id')
                    ->leftJoin('sec_menu_items', 'sec_role_permissions.menu_id', '=', 'sec_menu_items.menu_id')
                    ->where('sec_user_access_tbls.fk_user_id', $user->id)
                    ->select([
                        'sec_menu_items.menu_id',
                        'sec_menu_items.menu_title',
                        'sec_menu_items.module',
                        'sec_menu_items.parent_menu',
                        'sec_role_permissions.create',
                        'sec_role_permissions.read',
                        'sec_role_permissions.edit',
                        'sec_role_permissions.delete',
                    ])->orderBy('menu_id','ASC')
                    ->get();

                $userData = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'email_verified_at' => $user->email_verified_at,
                    'authority' => $rolesPivot ? [strval($rolesPivot->role_id)] : [],
                    //'authority' => [strval(2)],
                   // 'authority' => strval($rolesPivot->role_id),
                    'role_name' => $user->roles->pluck('name')->first(),
                    'permissions' =>$userAccess?$userAccess:[],
                ];


                if ($request->input('is_remember')) {
                    $cookie = cookie('auth_token', $token->plainTextToken, 60 * 24 * 30); // 30 days expiration

                    return ApiResponse::success([
                        'user' => $userData,
                        'token' => $token->plainTextToken,
                        'token_type' => 'Bearer',
                    ])->withCookie($cookie);
                }
                return ApiResponse::success([

                    'user' => $userData,
                    'token' => $token->plainTextToken,
                    'token_type' => 'Bearer',

                ])->withCookie($cookie);
            } else {
                return ApiResponse::error(404, 'User not found', 'The specified user does not exist.');
            }
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

    public function logout(Request $request)
    {

        $user = $request->user;

        try {

            $user->currentAccessToken()->delete();
            Logger::createLog( "{$user->name} logout from CMS",'read','user',$user);

            if ($request->hasCookie('auth_token')) {
                return response()->json(['message' => 'Successfully logged out'])
                    ->withCookie(cookie()->forget('auth_token'));

            }

            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }


    public function user(Request $request)
    {
        try {
            $user = $request->user;
            return ApiResponse::success($user, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');

        }

    }
    public function flushSession(Request $request)
    {
        $request->session()->flush();

        return redirect('/');
    }

}
