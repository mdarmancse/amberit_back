<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Http\Helpers\Logger;
use App\Models\SecMenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecMenuItemController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = SecMenuItem::query();
            $menuItems = $query->orderBy('sort_order','ASC')->get();

            $formattedMenuItems = $this->formatMenuItems($menuItems);

            return ApiResponse::success($formattedMenuItems, null, 'Menu items retrieved successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(500, 'Internal Server Error', $e->getMessage());
        }
    }
    public function getParentMenuDropdown(Request $request)
    {

        try {

            $parentMenus=SecMenuItem::select('menu_id as value','menu_title as label')->where('parent_menu',null)->get();

            return ApiResponse::success($parentMenus,null, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
    public function getAllMenu(Request $request)
    {
        try {
            $query = SecMenuItem::query();

            if ($request->has('pageIndex') && $request->has('pageSize')) {
                $pageIndex = $request->input('pageIndex');
                $pageSize = $request->input('pageSize');
                $query->skip(($pageIndex - 1) * $pageSize)->take($pageSize);
            }

            if ($request->has('query') ) {
                $keyword = $request->input('query');
                $query->where(function ($query) use ($keyword) {
                    $query->orWhere('menu_id', $keyword);
                    $query->orWhere('menu_title', 'like', "%{$keyword}%");
                });

            }

            $menuItems = $query->get();
            $totalCount = SecMenuItem::when($request->has('query'), function ($query) use ($request) {
                $keyword = $request->input('query');
                $query->where(function ($query) use ($keyword) {
                    $query->orWhere('menu_id', $keyword);
                    $query->orWhere('menu_title', 'like', "%{$keyword}%");
                });
            })->count();


            return ApiResponse::success($menuItems, $totalCount, 'Menu items retrieved successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(500, 'Internal Server Error', $e->getMessage());
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
                    'createby' => $menuItem->createby,
                    'created_at' => $menuItem->created_at,
                    'updated_at' => $menuItem->updated_at,
                    'subMenu' => $this->formatMenuItems($menuItems, $menuItem->menu_id),
                ];

                $formatted[] = $formattedItem;
            }
        }

        return $formatted;
    }



    public function show(Request $request)
    {
       // dd($id);

        $id=$request->id;

        try {
            $menuItem = SecMenuItem::where('menu_id',$id)->first();

            return ApiResponse::success($menuItem, null, 'Menu item retrieved successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(404, 'Menu item not found', 'The specified menu item does not exist.');
        }
    }

    public function store(Request $request)
    {


        try {
            $validatedData = $this->validateSecMenuItem($request);
            $validatedData['module'] = isset($request->module) ? $request->module : $request->menu_title;
            $validatedData['parent_menu'] = isset($request->parent_menu) ? $request->parent_menu :null;
            $validatedData['createby']=Auth::guard('sanctum')->user()->id;

            $menuItem = SecMenuItem::create($validatedData);
            if ($menuItem){
                Logger::createLog( $request->menu_title,'create','SecMenuItem',$request->all());
            }

            return ApiResponse::success($menuItem, 201, 'Menu item created successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(400, 'Bad Request', $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        try {
            $id = $request->menu_id;

            $validatedData = $this->validateSecMenuItem($request);

            $menuItem = SecMenuItem::where("menu_id", $id)->first();
            $oldData = $menuItem->getAttributes();
            if ($menuItem) {
                $menuItem->update($validatedData);

                    Logger::createLog( $menuItem->menu_title,'update','SecMenuItem',$request->all(),$oldData);

                return ApiResponse::success($menuItem, 200, 'Menu item updated successfully.');
            } else {
                return ApiResponse::error(404, 'Menu item not found', 'The specified menu item does not exist.');
            }

        } catch (\Throwable $e) {
            return ApiResponse::error(400, 'Bad Request', $e->getMessage());
        }
    }


    public function destroy(Request $request, $id)
    {
        try {
            $menuItem = SecMenuItem::findOrFail($id);
            $menuItem->delete();

            return ApiResponse::success(null, 204, 'Menu item deleted successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(500, 'Internal Server Error', $e->getMessage());
        }
    }

    private function validateSecMenuItem(Request $request)
    {
        return $request->validate([
            'menu_title' => 'required',
//            'page_url' => 'required',
            'module' => 'nullable',
            'parent_menu' => 'nullable|exists:sec_menu_items,menu_id',

        ]);
    }
}
