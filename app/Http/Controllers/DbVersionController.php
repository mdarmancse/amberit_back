<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Http\Helpers\Logger;
use App\Models\DbVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DbVersionController extends Controller
{
    public function getDbVersionList(Request $request)
    {
        try {

            $list = DbVersion::where(['api_version'=>'v2'])->orderBy('id','DESC')->get();

            return ApiResponse::success(['data'=>$list],null, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }


    public function update(Request $request)
    {
        try {
            $id = $request->id;

          //  dd($request->all());
            $request->validate([
                'db_version' => 'required|max:255',
            ]);

            $dbVersion = DbVersion::find($id);
            $oldData = $dbVersion->getAttributes();
            if ($dbVersion) {
                $dbVersion->update([
                    'db_version' => $request->db_version,
                    'updated_by' => Auth::guard('sanctum')->user()->id,
                ]);


                Logger::createLog( $dbVersion->api_name,'update','DbVersion',$dbVersion,$oldData);


                return ApiResponse::success($dbVersion, null, 'DB Version updated successfully');
            } else {
                return ApiResponse::error(404, 'Api not found', 'The specified api does not exist.');
            }
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }

}
