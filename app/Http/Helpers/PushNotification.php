<?php
// ApiResponse.php

namespace App\Http\Helpers;

use Illuminate\Support\Facades\Storage;

class PushNotification
{
    public static function Send($notification_id,$title, $text, $resourceUrl=null, $thumbnail)
    {

        $FCMApiKey = config('services.FCMApi.key');
        $FCMApiUrl = config('services.FCMApi.url');

        $topic='';
        if (env('APP_ENV') === 'local'){
            $topic = "/topics/test_fcm";
        }
        if (env('APP_ENV') === 'production'){
            $topic = "/topics/general_channel";
        }



        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $FCMApiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                    "to":"' . $topic . '",
                    "content_available": true,
                    "data": {
                        "notificationType": "large",
                        "notificationId": "' . $notification_id . '",
                        "button": "false",
                        "resourceUrl": "' . $resourceUrl . '",
                        "playNowUrl": "",
                        "watchLaterUrl": "",
                        "notificationHeader": "' . $title . '",
                        "notificationText": "' . $text . '",
                        "thumbnail": "' . $thumbnail . '"
                        "image": "' . $thumbnail . '"
                    },
                    "notification": {
                        "title": "' . $title . '",
                        "body": "' . $text . '",
                        "image": "' . $thumbnail . '",
                        "category": "Others i.e beta user, overlay etc"
                    }
                }',
            CURLOPT_HTTPHEADER => array(
                "Authorization: $FCMApiKey",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
