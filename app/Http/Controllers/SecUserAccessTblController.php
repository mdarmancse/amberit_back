<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Http\Helpers\Logger;
use App\Models\SecUserAccessTbl;
use Illuminate\Http\Request;

class SecUserAccessTblController extends Controller
{

    public function update(Request $request,$id)
    {
        try {
            $validatedData = $request->validate([
                'fk_role_id' => 'required',
                'fk_user_id' => 'required',
            ]);
            $oldData = SecUserAccessTbl::findOrFail();
            $userAccess = SecUserAccessTbl::updateOrCreate(
                ['id' => $id],
                $validatedData
            );
            if ($userAccess){
                Logger::createLog( $request->role_id,'update','SecUserAccessTbl',$request->all(),$oldData);
            }

            return ApiResponse::success($userAccess, 200, 'User access updated successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(400, 'Bad Request', $e->getMessage());
        }
    }


}
