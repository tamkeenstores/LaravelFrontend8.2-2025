<?php

namespace App\Helper;
use Config;

class NotificationHelper
{
    
    static function whatsappmessageContent($number,$template,$lang) {
        $postdata = [
           "recipient" => [
                "contact" => $number, 
                "channel" => "whatsapp" 
            ], 
            "content" => [
                "type" => "template", 
                "name" => $template, 
                "language" => [
                    "code" => $lang 
                ],
            ] 
        ]; 
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, config('unifonic.whatsapp.link'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($postdata));
        //curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n    \"recipient\": {\n      \"contact\": \"$number\",\n      \"channel\": \"whatsapp\"\n    },\n    \"content\": {\n      \"type\": \"text\",\n      \"text\": \"$message\"\n    }\n}");
        
        $headers = array();
        $headers[] = 'Publicid: '.config('unifonic.whatsapp.publickey');
        $headers[] = 'Secret: '.config('unifonic.whatsapp.secretkey');
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            // echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
    
    static function whatsappmessageImage($number,$template,$lang, $image = false){
        
        $postdata = [
           "recipient" => [
                "contact" => $number, 
                "channel" => "whatsapp" 
            ], 
            "content" => [
                "type" => "template", 
                "name" => $template, 
                "language" => [
                    "code" => $lang 
                ], 
                "components" => [] 
            ] 
        ]; 
         if($image){
            $postdata['content']['components'][] = [
                        "type" => "header", 
                        "parameters" => [
                            ["type"=> "image",
                            "url"=> $image,
                            ],
                        ]
                    ];
                    
            $postdata['content']['components'][] = [
                "type" => "options", 
                "parameters" => [
                    [
                        "value"=> "https://tamkeenstores.com.sa/ar",
                        "subType"=> 'url',
                        "index"=> 1,
                    ],
                ]
            ];
            
        }
        //return json_encode($postdata);
        //print_r(json_encode($postdata));
 
 
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, config('unifonic.whatsapp.link'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($postdata));
        //curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n    \"recipient\": {\n      \"contact\": \"$number\",\n      \"channel\": \"whatsapp\"\n    },\n    \"content\": {\n      \"type\": \"text\",\n      \"text\": \"$message\"\n    }\n}");
        
        $headers = array();
        $headers[] = 'Publicid: '.config('unifonic.whatsapp.publickey');
        $headers[] = 'Secret: '.config('unifonic.whatsapp.secretkey');
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            // echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
    
    static function whatsappmessage($number,$template,$lang,$params,$header = false,$otp =false, $image = false){
        
        $postdata = [
           "recipient" => [
                "contact" => $number, 
                "channel" => "whatsapp" 
            ], 
            "content" => [
                "type" => "template", 
                "name" => $template, 
                "language" => [
                    "code" => $lang 
                ], 
                "components" => [
                    [
                        "type" => "body", 
                        "parameters" => $params
                    ],
                ] 
            ] 
        ]; 
        if($otp){
            $postdata['content']['components'][] = [
                        "type" => "options", 
                        "parameters" => [
                            [
                                "value"=> $otp,
                                "subType"=> "url",
                                "index"=> 0
                            ],
                        ]
                    ];
            
        }
        if($header){
            $postdata['content']['components'][] = [
                        "type" => "header", 
                        "parameters" => [
                            ["type"=> "file",
                            "fileName"=> "TKS$header.pdf",
                            "url"=> "https://partners.tamkeenstores.com.sa/api/order/downloadPDF/$header",
                            "text" => "Invoice"
                            ],
                        ]
                    ];
            
        }
         if($image){
            $postdata['content']['components'][] = [
                        "type" => "header", 
                        "parameters" => [
                            ["type"=> "image",
                            "url"=> $image,
                            ],
                        ]
                    ];
            
        }
        //return json_encode($postdata);
        //print_r(json_encode($postdata));
 
 
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, config('unifonic.whatsapp.link'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($postdata));
        //curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n    \"recipient\": {\n      \"contact\": \"$number\",\n      \"channel\": \"whatsapp\"\n    },\n    \"content\": {\n      \"type\": \"text\",\n      \"text\": \"$message\"\n    }\n}");
        
        $headers = array();
        $headers[] = 'Publicid: '.config('unifonic.whatsapp.publickey');
        $headers[] = 'Secret: '.config('unifonic.whatsapp.secretkey');
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            // echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
    
    static function sms($number, $message)
    {
        $username = Config::get('sms.sentsms.username');
        $password = Config::get('sms.sentsms.password');
        $senderid = Config::get('sms.sentsms.senderid');
        $url = Config::get('sms.sentsms.url');
        // print_r($url);die();
        $request  = 'user=' .$username;     
        $request  .= '&pwd=' .$password;
        if($senderid){
            $request  .= '&senderid=' .$senderid;
        }
        $request  .= '&mobileno=' .$number;
        $request  .= '&msgtext=' .urlencode($message);
        $request  .= '&priority=High&CountryCode=ALL';
        $url = $url .'?' .$request;
        $cURLConnection = curl_init();

        curl_setopt($cURLConnection, CURLOPT_URL, $url);
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($cURLConnection);
        curl_close($cURLConnection);
        return $result;
    }
    
    public static function global_notification($tokenData, $title, $message, $image, $link = '', $type = '', $slug = '', $tokenDevice = false) {
        $clientEmail = 'firebase-adminsdk-r1hyt@tamkeenstores-reactnative-2023.iam.gserviceaccount.com';
        $privateKey = "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCfwnG1ywK9EVwo\nEQifMkbdNGaecAcY6poZY/HSKW2kyI+oNMswAHo1jNQ1Bbjhy9+gdfm1m8MTo7+a\nWCVKkaboM926X4aH7s7Ut+UwgHIkmNyvEJ5YiKcc7tdrXs0b110L2w4o8B/0pXp/\nAEi55qdMXDHBukN6c0XigRcuxanJDtmX6uN9nSyM6yD7xyt8Ij4vtTVyiwdOsRTp\nIbDAqW7hfp0xO7foSqTAOPKSSvWnSJizO3qgsvfmRDfeSc+f6MmTyL5m1LPfyeGG\npW2WS5A5kc5RvuBSPfOO59yaubQT/dt+6CNfqymxBhTmB46reEpQBjpmsizsI7QE\nxrq+6rm/AgMBAAECggEAJV9UQGW8P0RnXYwWiz80qpyOgPPOBTskT1o9AJ/mYUz1\npjCrc5A5BnvqTHkJNfD+YEgY4S4N5XNB6DZEivwlol43uViNXRE1WzelqsHxcbQ8\ns8tcb3OQPEl3JUvpCWFJB/P3jQYXQ6DOVhVX7f0SF9/dV+ddz7BVodX2+V8OB9VN\n3W7zHMshH57/kqDXDsWMddvvR3073gdbljpHt3O0JjxEXQjVf/z6dfYbOq8ro4YH\nq9qWDhaTpd+LWO2F6rWgCSrZavSc1FmvG8h3HIaELO9ahF2fRjh+bzD8jKHk2YBn\nxbEpMGOQAKUx8J686TBCAz/2HgSHXRtq3HzZWkx9wQKBgQDKSfbfeqlhOM5e5v8+\njygXzdDU7LyO3W/hglbwP7cibipLN1E5FDUX9t8Hze45l+kEDYM7kEK6asa/Yv4f\n6DNMiEabyELGYwnNK+kshlOs3y7/b8yVlA1fsiYcpI7LiM5sGhYg7mxDD0nZUQZQ\nvlpchuXTJIhP9dMfMBAbUcdYHwKBgQDKLal3QAN5qXAlSpejujAUytmwGWSudOhn\n/bZl1kRpCfCsDtXlyuAf+2tuJuRAcszImOyyZ3EyRTfAgn6m4WDqlqxJ1HzsyV6n\nkxlQoBAwX9IcWadKKT7pD9AZEcu0NHeiMCfGAJ7b3+qCpxPiicsRraRL1yJPJoav\nHY5EEWrqYQKBgQCL2jj4ZjhGA90BVZhvqs9gLaXMH2N5LfSUpuTuMk6tWhaZa8QW\nvza5u66UGbYyUSkC6UiqXGEVuo5vcMQaURFuPuT5/KjVuVDkbRBG/RNKd/5pEOUT\nIIlieKrKsKYcZxe3Ow3DdLKaZi57NP41wnR8dbLcl+w5w81TSYpJKO+1pwKBgQDF\nO7J9EUfSokczOgegw/wv7IxdTQh5YLdw64PK6TKnvfi6AWcN29K1oJJ3TR2S+etp\nLjaK/Hrjbb8r4KIprujbMc414ENWxEPA+rHRg7UHXBMfcR7QLFihCLocGs48qIql\nAJFsiiJvoYunldLCR2aBgoIrMl7YFF+D6jNlmBBTQQKBgH9KhGHAvnbrrICN47m5\n1sebYgomfIpxbZlbNQup9QNuOqqcVAMratsa60h4iWzrIleSkz0jouM+B4MWiSan\n8ualCM/9ft4SDd7crbreYLBxoXXqhxgqsxXImTXcKHZ06jCQveKzZkDehDIFBEPD\nq19t1N0/gfA3FllsTAFpK/t2\n-----END PRIVATE KEY-----\n";
        $tokenUrl = 'https://oauth2.googleapis.com/token';
        $projectId = 'tamkeenstores-reactnative-2023';
        
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];
        $claim = [
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $tokenUrl,
            'exp' => time() + 3600,
            'iat' => time()
        ];

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($header)));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($claim)));

        $signature = '';
        openssl_sign("$base64UrlHeader.$base64UrlPayload", $signature, str_replace('\\n', "\n", $privateKey), 'sha256');
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $jwt = "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $accessToken = json_decode($response, true)['access_token'] ?? '';

        $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';
        $requestHeaders = [
            "Authorization: Bearer " . $accessToken,
            "Content-Type: application/json"
        ];

        $tokenData = array_unique($tokenData);
        $responses = [];
        foreach ($tokenData as $token) {
            $postRequest = [
                "message" => [
                    "notification" => [
                        "title" => $title,
                        "body" => $message,
                        "image" => $image
                    ],
                    "data" => [
                        "icon" => $image,
                        "category" => $link,
                        "clickAction" => $link,
                        'type' => $type,
                        'slug' => $slug
                    ],
                    "token" => $token
                ]
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postRequest));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
            $response = curl_exec($ch);
            curl_close($ch);

            $responses[] = json_decode($response, true);
        }

        return $responses;
    }
    
    // // push notification
    // static function global_notification($tokenData,$title,$message,$image,$link = '',$type = '',$slug='',$tokenDevice=false){
    //     $url = "https://fcm.googleapis.com/fcm/send";
    //     // $cloud_messaging_server_key = "key=AAAABcbuV5E:APA91bH9dwp--iO3luOydWlUmHzjjCKuz-Do1Gf-bP6l9OCP-seyFDPpW1PUCR3bsVYGfB8EkeOFsQ9taCtXidpfrM0M_VcpEbIsXdXZcPXNxSM4VIaC8dn5qFTIq8NFrWUnTXMZQOw0";
    //     $cloud_messaging_server_key = "key=" . config('notification.notification_key');
    //     $request_headers = array(
    //         "Authorization:" . $cloud_messaging_server_key,
    //         "Content-Type: application/json"
    //     );
        
    //     if($tokenDevice){
    //         $postRequest = [
    //         "notification" => [
    //             // "id" => "",
    //             "title" => $title,
    //             "body" =>  $message,
    //             // "priority" => "high",
    //             // "sound" => "default",
    //             // "vibrate" => true,
    //             // "vibration" => 500,
    //             // "vibrationPattern" => [300, 500],
    //             "image" => $image,
    //             // "android" => [
    //             //     "image" => $image,
    //             // ],
    //         ],
    //         // "data" => [
    //         //     "icon" => $image,
    //         //     "category" => $link,
    //         //     "clickAction" => $link,
    //         //     'type' => $type,
    //         //     'slug' => $slug
    //         // ],
    //         "registration_ids" =>  $tokenData
    //     ];
    //     }else{
    //         //$slug = '';
            
    //         $postRequest = [
    //         "notification" => [
    //             // "id" => "",
    //             "title" => $title,
    //             "body" =>  $message,
    //             "priority" => "high",
    //             "sound" => "default",
    //             "vibrate" => true,
    //             "vibration" => 500,
    //             "vibrationPattern" => [300, 500],
    //             "image" => $image,
    //             "android" => [
    //                 "image" => $image,
    //             ],
    //         ],
    //         "data" => [
    //             "icon" => $image,
    //             "category" => $link,
    //             "clickAction" => $link,
    //             'type' => $type,
    //             'slug' => $link
    //         ],
    //         "registration_ids" =>  $tokenData
    //     ];
    //     }
        
        
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postRequest));
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
    //     $season_data = curl_exec($ch);
    //     curl_close($ch);
    //     $json = json_decode($season_data, true);
    //     // print_r($json);
    //     return $json;
    // }
}