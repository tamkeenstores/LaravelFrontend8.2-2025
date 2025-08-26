<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NotificationToken;
use App\Helper\NotificationHelper;
use Illuminate\Support\Facades\Cache;
use App\Models\CacheStores;

class NotificationApiController extends Controller
{
    
    public function sendNotification() {
        
        // $tokenData = ['c5BBE2IGRzCidSqz4UPNrm:APA91bEeyKd7-I6IpVTx7Cj080iyw3jw_2gev7wMaqngpYUpwXTK2Pu27r28wFY6YcJ_yKTHVuv_MLbqEsQkv6Rrcw7g3Qd_g_V-SlTD2ruFoerOs3MI9rOwaJF2gVPVN0XOHkEfJuyN'];
        $tokenData = NotificationToken::where('device','!=','Website')->pluck('token')->toArray();
        $data = NotificationHelper::global_notification($tokenData, 'Notification Title 123', 'Notification message 123', 'https://react.tamkeenstores.com.sa/assets/new-media/2ab592fe27552e8a493b9c9037ddcc9b1708257241.webp');
        
        $tokenData = NotificationToken::where('device','Website')->pluck('token')->toArray();
        $data = NotificationHelper::global_notification($tokenData, 'Notification Title 12', 'Notification message 12', 'https://react.tamkeenstores.com.sa/assets/new-media/2ab592fe27552e8a493b9c9037ddcc9b1708257241.webp','website');
    }
    
    // public function notificationToken($token, $device, $userid = '') {
    //     $success = false;
    //     $notification = NotificationToken::where('token', $token)->first();
    //     if($notification) {
    //         $useridcheck  = $notification->where('userid', $userid);
    //         if(!$useridcheck) {
    //             $notification->user_id = $userid;
    //             $notification->update();
    //         }
    //         $success = true;
    //     }
    //     else {
    //         $noti = NotificationToken::create([
    //             'user_id' => $userid ? $userid : null,
    //             'token' => $token,
    //             'device' => $device ? $device : null,
    //             'status' => 0,
    //         ]);
    //         $success = true;
    //     }
        
    //     $response = [
    //         'success' => $success,
    //         'token' => $token
    //     ];
    //     $responsejson=json_encode($response);
    //     $data=gzencode($responsejson,9);
    //     return response($data)->withHeaders([
    //         'Content-type' => 'application/json; charset=utf-8',
    //         'Content-Length'=> strlen($data),
    //         'Content-Encoding' => 'gzip'
    //     ]);
    // }
    
    public function notificationToken($token, $device = null, $userid = null)
    {
        $cacheKey = "notification_token:$token";
    
        // Try cache first
        $notification = Cache::remember($cacheKey, 86400, function () use ($token) {
            return NotificationToken::firstWhere('token', $token);
        });
    
        if ($notification) {
            if ($notification->user_id !== $userid) {
                $notification->update(['user_id' => $userid]);
                // Update cache after DB update
                Cache::put($cacheKey, $notification, 86400);
            }
        } else {
            $notification = NotificationToken::create([
                'user_id' => $userid,
                'token'   => $token,
                'device'  => $device,
                'status'  => 0,
            ]);
            // Cache the newly created record
            Cache::put($cacheKey, $notification, 86400);
        }
    
        $response = [
            'success' => true,
            'token'   => $token,
        ];
    
        $compressed = gzencode(json_encode($response), 9);
    
        return response($compressed)->withHeaders([
            'Content-Type'     => 'application/json; charset=utf-8',
            'Content-Length'   => strlen($compressed),
            'Content-Encoding' => 'gzip',
        ]);
    }
}
