<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Models\OtpLog;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function getIosOtpLog(Request $request)
    {

        try {
            $list = OtpLog::select('id', 'msisdn', 'otp', 'otp_expire_time')
                ->where('msisdn', 'like', '%8801958160964%')
                ->latest('otp_expire_time')
                ->first();

            return ApiResponse::success($list,1, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }
}
