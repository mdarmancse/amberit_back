<?php


namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Http\Helpers\Logger;
use App\Models\Role;
use App\Models\SecRolePermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SecRolePermissionController extends Controller
{
    public function index()
    {
        try {
            $rolePermissions = SecRolePermission::all();
            return ApiResponse::success($rolePermissions, null, 'Role permissions retrieved successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(500, 'Internal Server Error', $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $rolePermission = SecRolePermission::findOrFail($id);
            return ApiResponse::success($rolePermission, null, 'Role permission retrieved successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(404, 'Not Found', 'Role permission not found.');
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'role_id' => 'required',
                'menu_id' => 'required',
                'read' => 'required',
                'create' => 'required',
                'edit' => 'required',
                'delete' => 'required',
                'createby' => 'required',
            ]);

            $rolePermission = SecRolePermission::create($validatedData);
            if ($rolePermission){
                Logger::createLog( $request->role_id,'create','SecRolePermission',$request->all());
            }

            return ApiResponse::success($rolePermission, 201, 'Role permission created successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(400, 'Bad Request', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'role_id' => 'required',
                'menu_id' => 'required',
                'read' => 'required',
                'create' => 'required',
                'edit' => 'required',
                'delete' => 'required',
                'createby' => 'required',
            ]);

            $rolePermission = SecRolePermission::findOrFail($id);
            $oldData = $rolePermission->getAttributes();
            $rolePermission->update($validatedData);
            if ($rolePermission){
                Logger::createLog( $request->role_id,'update','SecRolePermission',$request->all(),$oldData??[]);
            }

            return ApiResponse::success($rolePermission, 200, 'Role permission updated successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(400, 'Bad Request', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $rolePermission = SecRolePermission::findOrFail($id);
            $rolePermission->delete();

            return ApiResponse::success(null, 204, 'Role permission deleted successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(404, 'Not Found', 'Role permission not found.');
        }
    }


    public function setPermissions(Request $request)
    {
        try {
            DB::beginTransaction();
            $roleName = $request->input('role_name');
            $menuPermissions = $request->input('menu_permissions');

            $role = Role::firstOrCreate(['name' => $roleName,'slug'=>strtolower($roleName)]);

            $roleId = $role->id;

            foreach ($menuPermissions as $menuPermission) {
                $menu_id= $menuPermission['menu_id'];
                if (isset($menu_id)) {
                    SecRolePermission::updateOrCreate(
                        ['role_id' => $roleId, 'menu_id' => $menu_id, 'createby' => Auth::guard('sanctum')->user()->id],
                        $menuPermission
                    );
                }
            }
            if ($role){
                Logger::createLog( $request->role_name,'create','Role',$request->role_name);
                Logger::createLog( $request->role_name,'create','SecRolePermission',$request->all());

            }
            DB::commit();

            return ApiResponse::success(['message' => 'Permissions updated successfully'], 200, 'Permissions updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(500, 'Internal Server Error', $e->getMessage());
        }
    }
    public function updatePermissions(Request $request)
    {
        try {
            DB::beginTransaction();

            $id = $request->id;
            $roleName = $request->input('role_name');
            $menuPermissions = $request->input('menu_permissions');


            $role = Role::find($id);

            $oldDataRole=$role->getAttributes();

            if (!$role) {
                return ApiResponse::error(404, 'Role not found', 'The specified role does not exist.');
            }

            $role->update([
                'name' => $roleName,
                'slug' => strtolower($roleName)
            ]);
            $oldData=SecRolePermission::where('role_id',$id)->get();


            foreach ($menuPermissions as $menuPermission) {

                $menu_id = $menuPermission['menu_id'];

                if (isset($menu_id)) {

                    $data = [
                        'role_id' => $id,
                        'menu_id' => $menu_id,
                        'createby' => Auth::guard('sanctum')->user()->id,
                        'read' => $menuPermission['read'] ?? false,
                        'create' => $menuPermission['create'] ?? false,
                        'edit' => $menuPermission['edit'] ?? false,
                        'delete' => $menuPermission['delete'] ?? false,
                    ];



                    SecRolePermission::updateOrCreate(
                        ['role_id' => $id, 'menu_id' => $menu_id],
                        $data
                    );
                }
            }

            if ($role){
                Logger::createLog( $request->role_name,'update','Role',$role,$oldDataRole);
                Logger::createLog( $request->role_name,'update','SecRolePermission',$request->all(),$oldData??[]);
            }

            DB::commit();

            return ApiResponse::success(['message' => 'Permissions updated successfully'], 200, 'Permissions updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(500, 'Internal Server Error', $e->getMessage());
        }
    }

}
