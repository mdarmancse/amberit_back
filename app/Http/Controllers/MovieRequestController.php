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

            $query = MovieRequest::select('*')
                ->with(['user' => function ($query) {
                    $query->select('id', 'user_name');
                }])
                ->latest('id');

            if ($request->has('query') ) {
                $keyword = $request->input('query');
                $query->where(function ($query) use ($keyword) {
                    $query->orWhere('id', $keyword);
                    $query->orWhere('user_id', 'like', "%{$keyword}%");
                    $query->orWhere('movie_name', 'like', "%{$keyword}%");
                });

            }

            if ($request->has('pageIndex') && $request->has('pageSize')) {
                $pageIndex = $request->input('pageIndex');
                $pageSize = $request->input('pageSize');
                $query->skip(($pageIndex - 1) * $pageSize)->take($pageSize);
            }

            $roles = $query->get();
            $totalCount = MovieRequest::when($request->has('query'), function ($query) use ($request) {
                $keyword = $request->input('query');
                $query->where(function ($query) use ($keyword) {
                    $query->orWhere('id', $keyword);
                    $query->orWhere('user_id', 'like', "%{$keyword}%");
                    $query->orWhere('movie_name', 'like', "%{$keyword}%");
                });
            })->count();

            return ApiResponse::success($roles,$totalCount, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

}
