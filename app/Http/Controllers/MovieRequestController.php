<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Models\MovieRequest;
use Illuminate\Http\Request;

class MovieRequestController extends Controller
{
    public function getMovieRequestList(Request $request)
    {
        try {

            $query = MovieRequest::select('*')->latest('id');
            if ($request->has('pageIndex') && $request->has('pageSize')) {
                $pageIndex = $request->input('pageIndex');
                $pageSize = $request->input('pageSize');
                $query->skip(($pageIndex - 1) * $pageSize)->take($pageSize);
            }

            $roles = $query->get();
            $totalCount = MovieRequest::count();

            return ApiResponse::success($roles,$totalCount, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

}
