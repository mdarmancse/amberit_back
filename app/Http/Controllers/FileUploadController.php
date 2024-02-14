<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Http\Helpers\FileHelper;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class FileUploadController extends Controller
{
    public function __construct() {
        $this->privateKeyFileContent = Config::get('app.google_privateKeyFileContent');
        $this->assetBucketName = Config::get('app.cloud_assetBucketName');
        $this->videoBucketName = Config::get('app.cloud_videoBucketName');
    }
    public function uploadImage(Request $request)
    {

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $uploadedImage = $request->file('image');
        $destination = $request->destination;
        $url = FileHelper::uploadImage($uploadedImage,$destination);

        return response()->json([
            'success' => true,
            'url' => $url,

        ]);
    }
    public function uploadGcpImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $uploadedImage = $request->file('image');
        $destination = $request->destination;
        $bucketName = $this->assetBucketName;
        $privateKeyFileContent=$this->privateKeyFileContent;


        $thumbnail = FileHelper::uploadImageToGoogleStorage($privateKeyFileContent, $bucketName, $uploadedImage, $destination);
        return response()->json([
            'success' => true,
            'url' => $thumbnail,

        ]);
    }

    public function uploadGcpVideo(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,mov|max:204800',
        ]);
        $uploadedImage = $request->file('video');
        $destination = $request->destination;
        $bucketName = $this->assetBucketName;
        $privateKeyFileContent=$this->privateKeyFileContent;


        $thumbnail = FileHelper::uploadVideoToGoogleStorage($privateKeyFileContent, $bucketName, $uploadedImage, $destination);

      if ($thumbnail['success']){
          return response()->json([
              'success' => true,
              'url' => $thumbnail['url'],
              'duration' => $thumbnail['duration'],

          ]);
      }  else{
          return response()->json([
              'success' => false,
              'url' => null,

          ]);
      }
    }

    public function uploadVideo(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,m3u8|max:204800',
        ]);

        $uploadedVideo = $request->file('video');
        $destination = $request->destination;

        $url = FileHelper::uploadVideo($uploadedVideo,$destination);

        return response()->json([
            'success' => true,
            'url' => $url,
        ]);
    }
    public function getSignedUrl(Request $request)
    {

        try {
            //$objectName=$request->file('object');
           // return $objectName;
            $objectName=$request->object;
            $storage = new StorageClient([
                'keyFile' => json_decode($this->privateKeyFileContent, true)
            ]);
            $bucket = $storage->bucket($this->videoBucketName);
            if ($objectName){
                $temp = explode(".", $objectName);
            }
            if (end($temp) == 'mp4' || end($temp) == 'mov') {
                $newfilename = $this->generateRandomString() . '-' . $this->generateRandomString() . '-' . $this->generateRandomString() . '-' . round(microtime(true)) . '.' . end($temp);
                $object = $bucket->object($newfilename);

                $signedURL = $object->signedUrl(
                    new \DateTime('15 min'),
                    [
                        'method' => 'PUT',
                        'contentType' => 'application/octet-stream',
                        'version' => 'v4',
                    ]
                );
                return ApiResponse::success(["signedURL"=>$signedURL,"fileName"=>$newfilename],null, 'Get signed url successfully');

            }else {

                return ApiResponse::error(400, 'Invalid File',);

            }

        }catch (\Throwable $e){
            return ApiResponse::error(500,  $e->getMessage(),'Something went wrong!');

        }

    }

    function generateRandomString($length = 8) {
        return substr(str_shuffle(str_repeat($x = '123456789abcdefghijklmnopqrstuvwxyz', ceil($length / strlen($x)))), 1, $length);
    }
}
