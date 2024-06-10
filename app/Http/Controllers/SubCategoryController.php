<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Http\Helpers\Logger;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function getSubCategoriesList(Request $request)
    {
        try {
            $query = SubCategory::select([
                'category_sub.id',
                'category_sub.category_id',
                'category_sub.sub_category_name',
                'category_sub.stb_thumbnail',
                'category_sub.sort_order',
                'category_sub.is_active',
                'category_sub.created_at',
                'category_sub.updated_at',
                'category.category_name',
            ])
                ->leftJoin('category', 'category_sub.category_id', '=', 'category.id')
                ->orderBy('category_sub.sort_order', 'DESC');

            if ($request->has('pageIndex') && $request->has('pageSize')) {
                $pageIndex = $request->input('pageIndex');
                $pageSize = $request->input('pageSize');
                $query->skip(($pageIndex - 1) * $pageSize)->take($pageSize);
            }

            $subCategories = $query->get();
            $totalCount = SubCategory::where(['is_active'=>1])->count();

            return ApiResponse::success($subCategories, $totalCount, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'category_id' => 'required|exists:category,id',
                'sub_category_name' => 'required|string|max:255',
            ]);

            $subCategory = SubCategory::create([
                'category_id' => $request->category_id,
                'sub_category_name' => $request->sub_category_name,
                'stb_thumbnail' => $request->stb_thumbnail,
                'sort_order' => $request->sort_order,
                'is_active' => $request->is_active??0,
            ]);

            if ($subCategory){
                Logger::createLog( $request->sub_category_name,'create','SubCategory',$request->all());
            }

            return ApiResponse::success($subCategory, null, 'SubCategory created successfully');
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

    public function show(Request $request)
    {
        try {

            $id = $request->id;
            $subCategory=SubCategory::select([
                'category_sub.id',
                'category_sub.category_id',
                'category_sub.sub_category_name',
                'category_sub.stb_thumbnail',
                'category_sub.sort_order',
                'category_sub.is_active',
                'category.category_name',
            ])
                ->leftJoin('category', 'category_sub.category_id', '=', 'category.id')
                ->where(['category_sub.id'=>$id])
                ->orderBy('category_sub.id', 'DESC')->first();
            $categories=Category::select('id as value','category_name as label')->where(['is_active'=>1])->get();

            $subData=[
                'id'=>$subCategory->id,
                'category_id'=>$subCategory->category_id,
                'sub_category_name'=>$subCategory->sub_category_name,
                'sort_order'=>$subCategory->sort_order,
                'is_active'=>$subCategory->is_active,
                'category_name'=>$subCategory->category_name,
                'stb_thumbnail'=>$subCategory->stb_thumbnail,
                'categories'=>$categories,
            ];
            if ($subCategory) {
                return ApiResponse::success($subData, null, 'SubCategory retrieved successfully');
            } else {
                return ApiResponse::error(404, 'SubCategory not found', 'The specified subcategory does not exist.');
            }
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

    public function update(Request $request)
    {
        try {

            $request->validate([
                'category_id' => 'required|exists:category,id',
                'sub_category_name' => 'required|string|max:255',
            ]);
            $id = $request->id;
            $subCategory = SubCategory::find($id);
            $oldData = $subCategory->getAttributes();
            if ($subCategory) {
                $subCategory->update([
                    'category_id' => $request->category_id,
                    'sub_category_name' => $request->sub_category_name,
                    'stb_thumbnail' => $request->stb_thumbnail,
                    'sort_order' => $request->sort_order,
                    'is_active' => $request->is_active,
                ]);

                if ($subCategory){
                    Logger::createLog( $request->sub_category_name,'update','SubCategory',$request->all(),$oldData);
                }

                return ApiResponse::success($subCategory, null, 'SubCategory updated successfully');
            } else {
                return ApiResponse::error(404, 'SubCategory not found', 'The specified subcategory does not exist.');
            }
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }



}
