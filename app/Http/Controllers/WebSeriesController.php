<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Http\Helpers\Logger;
use App\Models\Content;
use App\Models\WebSeries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebSeriesController extends Controller
{
    public function getWebSeriesDropdown(Request $request)
    {

        try {
            $result=WebSeries::select('id as value','series_name as label')->where('is_active',1)->get();

            return ApiResponse::success($result,null, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
    public function getWebSeriesList(Request $request)
    {
        try {

            $query = WebSeries::select('*')->latest('id');
            if ($request->has('pageIndex') && $request->has('pageSize')) {
                $pageIndex = $request->input('pageIndex');
                $pageSize = $request->input('pageSize');
                $query->skip(($pageIndex - 1) * $pageSize)->take($pageSize);
            }

            $roles = $query->get();
            $totalCount = WebSeries::count();

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
                'series_name' => 'required|string|max:255',
            ]);

            $result = WebSeries::create([
                'series_name' => $request->series_name,
                'total_sesson_no' => $request->total_sesson_no,
                'release_language' => $request->release_language,
                'sorting' => $request->sorting,
                'is_active' => $request->is_active,
                'created_by' => Auth::guard('sanctum')->user()->id,
                'updated_by' => Auth::guard('sanctum')->user()->id,
            ]);
            if ($result){
                Logger::createLog( $request->series_name,'create','WebSeries',$request->all());
            }


            return ApiResponse::success($result, null, 'WebSeries created successfully');
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
    public function show(Request $request)
    {
        try {
            $id = $request->id;
            $result = WebSeries::find($id);

            if ($result) {
                return ApiResponse::success($result, null, 'WebSeries retrieved successfully');
            } else {
                return ApiResponse::error(404, 'WebSeries not found', 'The specified WebSeries does not exist.');
            }
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }


    public function update(Request $request)
    {
        try {
            $request->validate([
                'series_name' => 'required|string|max:255',
                'is_active' => 'required|boolean',
            ]);

            $id = $request->id;
            $result = WebSeries::find($id);

            if ($result) {

                $oldData = $result->getAttributes();
                $result->update([
                    'series_name' => $request->series_name,
                    'total_sesson_no' => $request->total_sesson_no,
                    'release_language' => $request->release_language,
                    'sorting' => $request->sorting,
                    'is_active' => $request->is_active,
                    'updated_by' => Auth::guard('sanctum')->user()->id,
                ]);

                if ($result){
                    $contentIds = Content::where('tv_series_id', $id)->pluck('id');
                    Content::whereIn('id', $contentIds)->update(['tv_series_name' => $request->series_name]);
                    Logger::createLog( $request->series_name,'update','WebSeries',$request->all(),$oldData);
                }



                return ApiResponse::success($result, null, 'WebSeries updated successfully');
            } else {
                return ApiResponse::error(404, 'WebSeries not found', 'The specified WebSeries does not exist.');
            }
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(), 'Something went wrong!');
        }
    }

    public function destroy(Request $request)
    {
        try {
            $id = $request->id;
            $result = WebSeries::find($id);

            if ($result) {
                $result->delete();

                if ($result){
                    Logger::createLog( $result->series_name,'delete','WebSeries',$result);
                }


                return ApiResponse::success(null, null, 'WebSeries deleted successfully');
            } else {
                return ApiResponse::error(404, 'WebSeries not found', 'The specified WebSeries does not exist.');
            }
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
}
