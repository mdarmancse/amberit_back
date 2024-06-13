<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Http\Helpers\Logger;
use App\Models\Role;
use App\Models\SecMenuItem;
use App\Models\SecRolePermission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function getRolesListAll(Request $request)
    {
        try {
            $roles=Role::select('id as value','name as label','slug','created_at','updated_at')->orderBy('id','ASC')->get();

            return ApiResponse::success($roles,null, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
    public function getRolesList(Request $request)
    {
        try {

            $query = Role::select([
                'id',
                'name',
                'slug',
                'created_at',
                'updated_at',
            ])->orderBy('id','DESC');
            if ($request->has('query') ) {
                $keyword = $request->input('query');
                $query->where(function ($query) use ($keyword) {
                    $query->orWhere('id', $keyword);
                    $query->orWhere('name', 'like', "%{$keyword}%");
                });

            }
            if ($request->has('pageIndex') && $request->has('pageSize')) {
                $pageIndex = $request->input('pageIndex');
                $pageSize = $request->input('pageSize');
                $query->skip(($pageIndex - 1) * $pageSize)->take($pageSize);
            }

            $roles = $query->get();
            $totalCount = Role::when($request->has('query'), function ($query) use ($request) {
                $keyword = $request->input('query');
                $query->where(function ($query) use ($keyword) {
                    $query->orWhere('id', $keyword);
                    $query->orWhere('name', 'like', "%{$keyword}%");
                });
            })->count();

            return ApiResponse::success($roles,$totalCount, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:roles',
            ]);

            $role = Role::create([
                'name' => $request->name,
                'slug' => $request->slug,
            ]);
            if ($role){
                Logger::createLog( $request->name,'create','Role',$request->all());
            }


            return ApiResponse::success($role, null, 'Role created successfully');
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

    public function show(Request $request)
    {
        try {
            $id = $request->id;

            $role = Role::select('id', 'name as role_name', 'slug', 'created_at', 'updated_at')
                ->find($id);

            if ($role) {
                // Retrieve all menus
                $menus = SecMenuItem::orderBy('sort_order','ASC')->get();

                // Format the menu items to maintain the desired structure
                $formattedMenuItems = $this->formatMenuItems($menus);

                // Retrieve permissions for the specific role
                $permissions = SecRolePermission::select(
                    'menu_id',
                    'create',
                    'read',
                    'edit',
                    'delete'
                )
                    ->where('role_id', $id)
                    ->get();

                // Adjust the structure of permissions and merge them with the menu items
                foreach ($formattedMenuItems as &$menuItem) {
                    $menuId = $menuItem['menu_id'];
                    $menuItem['create'] = 0;
                    $menuItem['read'] = 0;
                    $menuItem['edit'] = 0;
                    $menuItem['delete'] = 0;
                    foreach ($permissions as $permission) {
                        if ($permission->menu_id === $menuId) {
                            $menuItem['create'] = $permission->create;
                            $menuItem['read'] = $permission->read;
                            $menuItem['edit'] = $permission->edit;
                            $menuItem['delete'] = $permission->delete;
                            break;
                        }
                    }
                }

                $roleData = [
                    'id' => $role->id,
                    'role_name' => $role->role_name,
                    'slug' => $role->slug,
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at,
                    'permissions' => $formattedMenuItems
                ];

                return ApiResponse::success($roleData, null, 'Role menus retrieved successfully');
            } else {
                return ApiResponse::error(404, 'Role not found', 'The specified role does not exist.');
            }
        } catch (\Throwable $e) {
            return ApiResponse::error(500, $e->getMessage(),'Something went wrong!');
        }
    }

    public function update(Request $request)
    {
        try {
            $id = $request->id;
            $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:roles,slug,' . $id,
            ]);

            $role = Role::findOrFail($id);

            $oldData = $role->getAttributes();
            if ($role) {
                $role->update([
                    'name' => $request->name,
                    'slug' => $request->slug,
                ]);

                    Logger::createLog( $request->name,'update','Role',$request->all(),$oldData?$oldData:[]);

                return ApiResponse::success($role, null, 'Role updated successfully');
            } else {
                return ApiResponse::error(404, 'Role not found', 'The specified role does not exist.');
            }
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

    public function destroy($id)
    {
        try {
            $role = Role::find($id);

            if ($role) {
                $role->delete();

                return ApiResponse::success(null, null, 'Role deleted successfully');
            } else {
                return ApiResponse::error(404, 'Role not found', 'The specified role does not exist.');
            }
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
    private function formatMenuItems($menuItems, $parentMenuId = null)
    {
        $formatted = [];

        foreach ($menuItems as $menuItem) {
            if ($menuItem->parent_menu == $parentMenuId) {
                $formattedItem = [
                    'menu_id' => $menuItem->menu_id,
                    'menu_title' => $menuItem->menu_title,
                    'module' => $menuItem->module,
                    'parent_menu' => $menuItem->parent_menu,
                    'create' => $menuItem->create,
                    'read' => $menuItem->read,
                    'edit' => $menuItem->edit,
                    'delete' => $menuItem->delete,
                    'subMenu' => $this->formatMenuItems($menuItems, $menuItem->menu_id),
                ];

                $formatted[] = $formattedItem;
            }
        }

        return $formatted;
    }


    public function setPermissions(Request $request, $roleId)
    {
        try {
            // Validate and set permissions for a role
            $role = Role::findOrFail($roleId);

            $validatedData = $request->validate([
                'create' => 'required|boolean',
                'read' => 'required|boolean',
                'edit' => 'required|boolean',
                'delete' => 'required|boolean',
            ]);

            $role->update($validatedData);

            if ($role){
                Logger::createLog( $role->name,'update','Role',$request->all());

            }

            return ApiResponse::success($role, 200, 'Role permissions updated successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(400, 'Bad Request', $e->getMessage());
        }
    }

}
