<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Http\Helpers\Logger;
use App\Models\Role;
use App\Models\SecUserAccessTbl;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;
class UserController extends Controller
{
//    public function getUsersList()
//    {
//        try {
//            $users = User::whereHas('roles', function ($query) {
//                $query->where('role_id', '!=', '1');
//            })->get()->map(function ($user) {
//                return [
//                    'id' => $user->id,
//                    'name' => $user->name,
//                    'email' => $user->email,
//                    'created_at' => $user->created_at,
//                    'updated_at' => $user->updated_at,
//                    'email_verified_at' => $user->email_verified_at,
//                ];
//            });
//
//
//            return ApiResponse::success($users, null, 'User list fetched successfully');
//
//        }catch (\Throwable $e){
//            return ApiResponse::error(500, 'Internal Server Error', $e->getMessage());
//
//        }
//
//
//
//    }

    public function getUsersListDropdown(Request $request)
    {
        try {
            $roles=User::select('id as value','name as label')->orderBy('id','ASC')->get();

            return ApiResponse::success($roles,null, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

    public function getUsersList(Request $request)
    {
        try {
            $query = User::select([
                'id',
                'name',
                'email',
                'phone',
                'email_verified_at',
                'created_at',
                'updated_at',
            ])->orderBy('id','DESC');

            if ($request->has('pageIndex') && $request->has('pageSize')) {
                $pageIndex = $request->input('pageIndex');
                $pageSize = $request->input('pageSize');
                $query->skip(($pageIndex - 1) * $pageSize)->take($pageSize);
            }

            $users = $query->get();
            $totalCount = User::count();

            return ApiResponse::success($users,$totalCount, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500, 'Internal Server Error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        try {

            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);
            if ($user){
                Logger::createLog( $request->name,'create','User',$request->all());
            }


            $role = Role::find($request->role);
            $user->roles()->attach($role);

            $assignUserRole = [
                'fk_role_id' => $role->id,
                'fk_user_id' =>$user->id,
            ];

            SecUserAccessTbl::updateOrCreate(
                ['fk_user_id' => $user->id],
                $assignUserRole
            );

            DB::commit();
          //  event(new Registered($user));

            return ApiResponse::success($user, null, 'User created successfully');

        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(500, 'Internal Server Error', $e->getMessage());
        }
    }


    public function show(Request $request)
    {
        try {
            $id = $request->query('id');
            $user = User::with('roles')->find($id);

            if (!$user) {
                return ApiResponse::error(404, 'User not found');
            }

            $rolesPivot = $user->roles->first() ? $user->roles->first()->pivot : null;

            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'email_verified_at' => $user->email_verified_at,
                'role' => $rolesPivot ? $rolesPivot->role_id : null,
                'role_name' => $user->roles->pluck('name')->first(),
                'roles' => Role::select('id as value', 'name as label', 'slug')->orderBy('id', 'ASC')->get(),
            ];

            return ApiResponse::success($userData, null, 'User details fetched successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();
            $id = $request->id;
            $user = User::find($id);
            $oldData = $user->getAttributes();
            if (!$user) {
                return ApiResponse::error(404, 'User not found');
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
                //'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            ]);

            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;

            if ($request->filled('password')) {
                $request->validate([
                    'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
                ]);
                $user->password = Hash::make($request->password);
            }

            $user->save();
            if ($user){
                Logger::createLog( $request->name,'update','User',$request->all(),$oldData);
            }

            // Update user roles
            $role = Role::find($request->role);

            if (!$role) {
                return ApiResponse::error(404, 'Role not found');
            }


            $user->roles()->sync([$role->id]);

            $assignUserRole = [
                'fk_role_id' => $role->id,
                'fk_user_id' =>$user->id,
            ];

            SecUserAccessTbl::updateOrCreate(
                ['fk_user_id' => $user->id],
                $assignUserRole
            );

            DB::commit();

            return ApiResponse::success($user, null, 'User updated successfully');
        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(500, 'Something went wrong', $e->getMessage());
        }
    }

    public function getUserRoleAndPermissions($user_id)
    {
        try {
            $userAccess = SecUserAccessTbl::leftJoin('sec_role_permissions', 'sec_user_access_tbls.fk_role_id', '=', 'sec_role_permissions.role_id')
                ->leftJoin('sec_menu_items', 'sec_role_permissions.menu_id', '=', 'sec_menu_items.id')
                ->where('sec_user_access_tbls.fk_user_id', $user_id)
                ->select([
                    'sec_menu_items.id',
                    'sec_menu_items.menu_id',
                    'sec_menu_items.menu_title',
                    'sec_menu_items.page_url',
                    'sec_menu_items.module',
                    'sec_menu_items.parent_menu',
                    'sec_menu_items.is_report',
                    'sec_role_permissions.create',
                    'sec_role_permissions.read',
                    'sec_role_permissions.edit',
                    'sec_role_permissions.delete',
                ])->orderBy('id','ASC')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $userAccess,
                'message' => 'User role, permissions, and menus retrieved successfully.',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not Found',
                'details' => $e->getMessage(),
            ], 404);
        }
    }
}
