<?php

namespace App\Vk;

class Apps
{

    const INVALID_AUTH_KEY = 3;
    const REQUIRED_PARAM = 1;
    const INVALID_APP_ID = 2;

    public static function getAuthKey(int $appId, int $viewerId)
    {
        $appSecret = self::getAppSecret();
        return md5( implode('_', [$appId, $viewerId, $appSecret]) );
    }

    public static function getAppId() {
        return env('APP_ORDER_ID', 5648408);
    }
    
    public static function getAppSecret()
    {
        return env('APP_ORDER_SECRET', '52k3aez1Bje5jtHApApd');
    }

    public static function isValidData(int $apiId, int $viewerId, string $authKey)
    {
        return $authKey == self::getAuthKey($apiId, $viewerId);
    }

    public static function fetchServerToken()
    {
        $url = 'https://oauth.vk.com/access_token';
        $p = [
            'client_id' => self::getAppId(),
            'client_secret' => self::getAppSecret(),
            'grant_type' => 'client_credentials',
            'v' => VkExecutor::V
        ];
        $url .= '?'.http_build_query($p);
        $opts = ['http' =>
            [
                'timeout' => 31,
                'ignore_errors' => true
            ]
        ];

        $context  = stream_context_create($opts);
        try {
            $result = file_get_contents($url, false, $context);
            $json = json_decode($result, true);
            if (isset($json['access_token'])) {
                return $json['access_token'];
            } else {
                \Log::error('Cant fetch access_token', [
                    'result' => $result,
                    'tag' => self::class
                ]);
                return '';
            }
        } catch (\Exception $e) {
            \Log::error('Cant fetch access_token', [
                'result' => $e->getCode().' '.$e->getMessage(),
                'tag' => self::class
            ]);
            return '';
        }
    }

    public static function getAppServerToken()
    {
        return \Cache::remember( 'access_token_'.self::getAppId().'_'.self::getAppSecret(), 60*24*2, function () {
            return self::fetchServerToken();
        } );
    }
}