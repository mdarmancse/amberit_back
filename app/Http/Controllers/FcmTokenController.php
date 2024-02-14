<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Http\Helpers\Logger;
use App\Http\Helpers\PushNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ContentResource;
use App\Models\Content;
use App\Models\FcmNotification;
use App\Http\Resources\NotificationResource;
use App\Models\ApiSetting;
use Google\Cloud\Storage\StorageObject;
use Google\Cloud\Storage\StorageClient;
class FcmTokenController extends Controller
{
    protected $bigQuery = null;
    protected $bigQueryKey = null;

    public function __construct() {
        $this->privateKeyFileContent = Config::get('app.google_privateKeyFileContent');
        $this->assetBucketName = Config::get('app.cloud_assetBucketName');
    }

    public function getNotification(Request $request)
    {
        try {
            $query = FcmNotification::select('*')->orderBy('id','desc');

            if ($request->has('pageIndex') && $request->has('pageSize')) {
                $pageIndex = $request->input('pageIndex');
                $pageSize = $request->input('pageSize');
                $query->skip(($pageIndex - 1) * $pageSize)->take($pageSize);
            }

            $contents = $query->get();
            $totalCount = FcmNotification::count();

            return ApiResponse::success($contents,$totalCount, 'Resource fetched successfully.');

        } catch (\Exception $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }


    public function sendFCMNotification(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string',
                'notificationtext' => 'required|string',
                //'resourceUrl' => 'required|string',
               // 'thumbnail' => 'image|mimes:jpeg,jpg,png,gif,svg|max:300',
            ]);
            $title=$request->title;
            $notificationtext=$request->notificationtext;
            $thumbnail=$request->thumbnail;
            $content_id = trim($request->input('content_id'));

            $contentInfo = Content::find($content_id);
            $share_url='';
            if ($contentInfo){
                $share_url = "https://tsports.com/shared/video/$contentInfo->share_url";

            }



            $lastId = FcmNotification::max('id');

            $newId = $lastId + 1;
            $thumbnailWithBaseURL="https://image.tsports.com/$thumbnail";

            $pushResponse=  PushNotification::Send($lastId,$title, $notificationtext, $share_url, $thumbnailWithBaseURL);
            $pushResponseDecoded = json_decode($pushResponse);
            //return $pushResponseDecoded->message_id;
            if ($pushResponseDecoded->message_id) {
                $notification = FcmNotification::create([
                    'id' => $newId,
                    'fcm_success_message_id'=>$pushResponseDecoded->message_id,
                    'notification_title' => $request->title,
                    'notification_text' => $request->notificationtext,
                    'content_id' => $request->content_id,
                    'resource_url' => $share_url,
                    'thumbnail' => $thumbnail,
                    'user_id' => Auth::guard('sanctum')->user()->id
                ]);
                if ($notification){
                    Logger::createLog($request->title,'create','FcmNotification',$request->all());
                }

            }

            return ApiResponse::success($notification,null, 'FCM notification sent successfully.');
        } catch (\Throwable $e) {
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');
        }
    }


}
