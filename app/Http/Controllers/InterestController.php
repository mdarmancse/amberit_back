<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Http\Helpers\Logger;
use App\Models\Category;
use App\Models\Interest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterestController extends Controller
{
    public function getInterestDropdown(Request $request)
    {

        try {
            $result=Interest::select('id as value','interest_name as label')->where('is_active',1)->get();

            return ApiResponse::success($result,null, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
    public function getInterestList(Request $request)
    {
        try {

            $query = Interest::select('*')->latest('id');
            if ($request->has('pageIndex') && $request->has('pageSize')) {
                $pageIndex = $request->input('pageIndex');
                $pageSize = $request->input('pageSize');
                $query->skip(($pageIndex - 1) * $pageSize)->take($pageSize);
            }

            $roles = $query->get();
            $totalCount = Interest::count();

            return ApiResponse::success($roles,$totalCount, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

    public function store(Request $request)
    {
        //dd($request->all());
        try {
            $request->validate([
                'interest_name' => 'required|string|max:255',
            ]);

            $result = Interest::create([
                'interest_name' => $request->interest_name,
                'is_active' => $request->is_active,
                'created_by' => Auth::guard('sanctum')->user()->id,
                'updated_by' => Auth::guard('sanctum')->user()->id,
            ]);
            if ($result){
                Logger::createLog( $request->interest_name,'create','Interest',$request->all());
            }


            return ApiResponse::success($result, null, 'Interest created successfully');
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
    public function show(Request $request)
    {
        try {
            $id = $request->id;
            $result = Interest::find($id);

            if ($result) {
                return ApiResponse::success($result, null, 'Interest retrieved successfully');
            } else {
                return ApiResponse::error(404, 'Interest not found', 'The specified interest does not exist.');
            }
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }


    public function update(Request $request)
    {
        try {
            $request->validate([
                'interest_name' => 'required|string|max:255',
                'is_active' => 'required|boolean',
            ]);

            $id = $request->id;
            $result = Interest::find($id);

            if ($result) {

                $oldData = $result->getAttributes();
                $result->update([
                    'interest_name' => $request->interest_name,
                    'is_active' => $request->is_active,
                    'updated_by' => Auth::guard('sanctum')->user()->id,
                ]);

                if ($result){
                    Logger::createLog( $request->interest_name,'update','Interest',$request->all(),$oldData);
                }



                return ApiResponse::success($result, null, 'Interest updated successfully');
            } else {
                return ApiResponse::error(404, 'Interest not found', 'The specified interest does not exist.');
            }
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(), 'Something went wrong!');
        }
    }

    public function destroy(Request $request)
    {
        try {
            $id = $request->id;
            $result = Interest::find($id);

            if ($result) {
                $result->delete();

                if ($result){
                    Logger::createLog( $result->interest_name,'delete','Interest',$result);
                }


                return ApiResponse::success(null, null, 'Interest deleted successfully');
            } else {
                return ApiResponse::error(404, 'Interest not found', 'The specified interest does not exist.');
            }
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
}
