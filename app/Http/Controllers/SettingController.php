<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Http\Helpers\Logger;
use App\Http\Resources\PackageResource;
use App\Models\Category;
use App\Models\PremiumPackage;
use App\Models\SettingModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function getSettingList(Request $request)
    {
        try {

            $query = SettingModel::select(['id','device_type','is_active','created_at','updated_at'])->latest('id');
            if ($request->has('pageIndex') && $request->has('pageSize')) {
                $pageIndex = $request->input('pageIndex');
                $pageSize = $request->input('pageSize');
                $query->skip(($pageIndex - 1) * $pageSize)->take($pageSize);
            }

            $data = $query->get();
            $totalCount = SettingModel::count();

            return ApiResponse::success($data,$totalCount, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
    public function store(Request $request)
    {
        try {
            $request->validate([
                'device_type' => 'required|max:255',

            ]);

            $data = SettingModel::create([
                'device_type' => $request->device_type,
                'terms_and_conditions' => $request->terms_and_conditions,
                'privacy_notice' => $request->privacy_notice,
                'faq' => $request->faq,
                'is_active' => $request->is_active??0,
                'created_by' => Auth::guard('sanctum')->user()->id,
                'updated_by' => Auth::guard('sanctum')->user()->id,
            ]);
            if ($data){
                Logger::createLog( $request->device_type,'create','SettingModel',$request->all());
            }


            return ApiResponse::success($data, null, 'Package created successfully');
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
    public function show(Request $request)
    {
        try {
            $id = $request->id;
            $data = SettingModel::findOrFail($id);

            // Check if the package exists
            if ($data) {
                return ApiResponse::success($data, null, 'Setting retrieved successfully');
            } else {
                return ApiResponse::error(404, 'Setting not found', 'The specified setting does not exist.');
            }
        } catch (\Throwable $e) {
            return ApiResponse::error(500, $e->getMessage(), 'Something went wrong!');
        }
    }



    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'device_type' => 'required|max:255',


            ]);

            $id = $request->id;
            $data = SettingModel::find($id);

            if ($data) {

                $oldData = $data->getAttributes();
                $data->update([
                    'device_type' => $request->device_type,
                    'terms_and_conditions' => $request->terms_and_conditions,
                    'privacy_notice' => $request->privacy_notice,
                    'faq' => $request->faq,
                    'is_active' => $request->is_active??0,
                    'updated_by' => Auth::guard('sanctum')->user()->id,
                ]);

                if ($data){
                    Logger::createLog( $request->device_type,'update','SettingModel',$request->all(),$oldData);
                }


                return ApiResponse::success($data, null, 'Settings updated successfully');
            } else {
                return ApiResponse::error(404, 'Setting not found', 'The specified setting does not exist.');
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
