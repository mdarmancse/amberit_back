<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Logger;
use App\Http\Helpers\ApiResponse;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Spatie\Activitylog\Models\Activity;

class CategoryController extends Controller
{

    public function getCategoriesDropdown(Request $request)
    {

        try {
            $categories=Category::select('id as value','category_name as label')->orderBy('sort_order','DESC')->where('is_active',1)->get();

            return ApiResponse::success($categories,null, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
    public function getCategoriesList(Request $request)
    {
        try {

            $query = Category::select([
                'id',
                'category_name',
                'category_logo',
                'sort_order',
                'is_active',
                'created_at',
                'updated_at',
            ])->latest('id');
            if ($request->has('pageIndex') && $request->has('pageSize')) {
                $pageIndex = $request->input('pageIndex');
                $pageSize = $request->input('pageSize');
                $query->skip(($pageIndex - 1) * $pageSize)->take($pageSize);
            }

            $roles = $query->get();
            $totalCount = Category::count();

            return ApiResponse::success($roles,$totalCount, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'category_name' => 'required|string|max:255',
                'category_logo' => 'nullable|string|max:255',
            ]);

            $category = Category::create([
                'category_name' => $request->category_name,
                'category_logo' => $request->category_logo,
                'sort_order' => $request->sort_order,
                'is_active' => $request->is_active,
                'created_by' => Auth::guard('sanctum')->user()->id,
                'updated_by' => Auth::guard('sanctum')->user()->id,
            ]);
            if ($category){
                Logger::createLog( $request->category_name,'create','Category',$request->all());
            }


            return ApiResponse::success($category, null, 'Category created successfully');
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
    public function show(Request $request)
    {
        try {
            $id = $request->id;
            $category = Category::find($id);

            if ($category) {
                return ApiResponse::success($category, null, 'Category retrieved successfully');
            } else {
                return ApiResponse::error(404, 'Category not found', 'The specified category does not exist.');
            }
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }


    public function update(Request $request)
    {
        try {
            $request->validate([
                'category_name' => 'required|string|max:255',
                'category_logo' => 'nullable|string|max:255',
                'sort_order' => 'required|integer',
                'is_active' => 'required|boolean',
            ]);

            $id = $request->id;
            $category = Category::find($id);

            if ($category) {

                $oldData = $category->getAttributes();
                $category->update([
                    'category_name' => $request->category_name,
                    'category_logo' => $request->category_logo,
                    'sort_order' => $request->sort_order,
                    'is_active' => $request->is_active,
                    'updated_by' => Auth::guard('sanctum')->user()->id,
                ]);

                if ($category){
                    Logger::createLog( $request->category_name,'update','Category',$request->all(),$oldData);
                }



                return ApiResponse::success($category, null, 'Category updated successfully');
            } else {
                return ApiResponse::error(404, 'Category not found', 'The specified category does not exist.');
            }
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(), 'Something went wrong!');
        }
    }

    public function destroy(Request $request)
    {
        try {
            $id = $request->id;
            $category = Category::find($id);

            if ($category) {
                $category->delete();

                if ($category){
                    Logger::createLog( $category->category_name,'delete','category',$category);
                }


                return ApiResponse::success(null, null, 'Category deleted successfully');
            } else {
                return ApiResponse::error(404, 'Category not found', 'The specified category does not exist.');
            }
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

}
