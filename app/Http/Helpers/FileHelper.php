<?php
// ApiResponse.php

namespace App\Http\Helpers;

use Exception;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use getID3;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Storage;

class FileHelper
{
    public static function uploadImage($file, $destination , $disk = 'public')
    {
        $filePath = 'uploads/images/'.$destination.'/' . $file->getClientOriginalName();
        Storage::disk($disk)->put($filePath, file_get_contents($file));

       // return Storage::disk($disk)->url($filePath);
        return $filePath;
    }



    public static function uploadVideo($file, $destination, $disk = 'gcs')
    {
        $filePath = 'uploads/videos/'.$destination.'/'. $file->getClientOriginalName();
        Storage::disk($disk)->put($filePath, file_get_contents($file));
         return Storage::disk($disk)->url($filePath);
        //return $filePath;
    }

    public static function uploadImageToGoogleStorage($privateKeyFileContent, $bucketName, $file, $destination)
    {
        try {
            $storage = new StorageClient([
                'keyFile' => json_decode($privateKeyFileContent, true)
            ]);

            $bucket = $storage->bucket($bucketName);

            $filenamewithextension = str_replace(' ', '-', $file->getClientOriginalName());

            $file_name = time() . '-' . $filenamewithextension;

            $fileContent = file_get_contents($file->getPathname());
            $cloudPath = 'images/test'.$destination . '/' . $file_name;

            $isSucceed = self::uploadFileContent($bucket, $fileContent, $cloudPath);

            if ($isSucceed) {
                return $cloudPath;
            } else {
                // Handle upload failure
                return null;
            }
        } catch (Exception $e) {
            // Handle exception, maybe log the error
            return null;
        }
    }
    public static function uploadVideoToGoogleStorage($privateKeyFileContent, $bucketName, $videoFile, $destination)
    {
        try {
            $storage = new StorageClient([
                'keyFile' => json_decode($privateKeyFileContent, true)
            ]);

            $bucket = $storage->bucket($bucketName);

            $filenamewithextension = str_replace(' ', '-', $videoFile->getClientOriginalName());
            $file_name = time() . '-' . $filenamewithextension;

            $fileContent = file_get_contents($videoFile->getPathname());
            $cloudPath = 'video/test/' . $destination . '/' . $file_name;

            $isSucceed = self::uploadFileContent($bucket, $fileContent, $cloudPath);

            if ($isSucceed) {
                $duration = self::getVideoDuration($fileContent);
                return [
                    'success' => true,
                    'url' => $cloudPath,
                    'duration' => $duration,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to upload video',
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private static function uploadFileContent($bucket, $fileContent, $cloudPath)
    {
        $object = $bucket->upload($fileContent, [
            'name' => $cloudPath
        ]);

        return $object->exists();
    }

    public static function getVideoDuration($fileContent)
    {
        try {
            $tempFile = tempnam(sys_get_temp_dir(), 'video');
            file_put_contents($tempFile, $fileContent);

            $getID3 = new getID3();
            $fileInfo = $getID3->analyze($tempFile);

            $seconds = isset($fileInfo['playtime_seconds']) ? $fileInfo['playtime_seconds'] : null;

            $duration = self::formatDuration($seconds);

            unlink($tempFile);

            return $duration;
        } catch (\Exception $e) {
            return null;
        }
    }

    private static function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

}
