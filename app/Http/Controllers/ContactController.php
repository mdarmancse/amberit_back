<?php

namespace App\Http\Controllers;


use App\Http\Helpers\ApiResponse;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function getContactList(Request $request)
    {
        try {

            $query = Contact::select('*')->latest('id');
            if ($request->has('query') ) {
                $keyword = $request->input('query');
                $query->where(function ($query) use ($keyword) {
                    $query->orWhere('id', $keyword);
                    $query->orWhere('user_id', 'like', "%{$keyword}%");
                    $query->orWhere('username', 'like', "%{$keyword}%");
                    $query->orWhere('mobile_number', 'like', "%{$keyword}%");
                    $query->orWhere('subject', 'like', "%{$keyword}%");
                });

            }
            if ($request->has('pageIndex') && $request->has('pageSize')) {
                $pageIndex = $request->input('pageIndex');
                $pageSize = $request->input('pageSize');
                $query->skip(($pageIndex - 1) * $pageSize)->take($pageSize);
            }

            $roles = $query->get();
            $totalCount = Contact::when($request->has('query'), function ($query) use ($request) {
                $keyword = $request->input('query');
                $query->where(function ($query) use ($keyword) {
                    $query->orWhere('id', $keyword);
                    $query->orWhere('user_id', 'like', "%{$keyword}%");
                    $query->orWhere('username', 'like', "%{$keyword}%");
                    $query->orWhere('mobile_number', 'like', "%{$keyword}%");
                    $query->orWhere('subject', 'like', "%{$keyword}%");
                });
            })->count();

            return ApiResponse::success($roles,$totalCount, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
}
