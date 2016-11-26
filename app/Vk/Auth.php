<?php


namespace App\Vk;


use Carbon\Carbon;
use Mockery\CountValidator\Exception;

class Auth
{
    protected static $session = null;
    
    public static function createSession(Request $request) {
        
        $userId = $request->getUserId();
        $appId = $request->getAppId();
        $groupId = $request->getGroupId();
        
        $data = implode('A', [$userId, $appId, $groupId]);
        $check = self::calcCheck(new Carbon(), $data);

        return $groupId.'_'.$check.'_'.$data;
    }
    
    public static function calcCheck(Carbon $now, $data) {
        $d1 = $now->format('d.m.Y');
        $prefix = md5($d1."_".env("APP_KEY"));
        $check = hash_hmac('sha256', $data, $prefix);
        return $check;
    }

    public static function init($token, $now = null) {

        $t = explode('_', $token);
        
        $check = $t[1] ?? '';
        $data = $t[2] ?? '';

        $keys = [];
        if (!($now instanceof Carbon)) {
            $now = new Carbon();
        }
        $keys[] = $now;
        $keys[] = $now->copy()->subDay(1);
        $keys[] = $now->copy()->subDay(2);

        foreach ($keys as $key) {
            $_check = self::calcCheck($key, $data);
            if ($check == $_check) {
                self::initFromData($data);
                return true;
            }
        }
        return false;
    }
    
    public static function groupId() {
        if (self::$session !== null) {
            return self::$session['group_id'];
        } else {
            throw new Exception('Session is not inited. Call Auth::initSession before using it');
        }
    }
    
    public static function userId() {
        if (self::$session !== null) {
            return self::$session['user_id'];
        } else {
            throw new Exception('Session is not inited. Call Auth::initSession before using it');
        }
    }

    public static function roots()
    {
        return [19039187];
    }

    protected static function getLimiterKey($token) {
        return 'limiter::'.$token;
    }
    
    public static function hit($token)
    {
        $key = self::getLimiterKey($token);
        \Cache::add($key, 1, 1);
        \Cache::increment($key);
    }
    
    public static function tooManyRequests($token, $maxPerMinute) {
        $key = self::getLimiterKey($token);
        if (\Cache::get($key, 0) > $maxPerMinute) {
            return true;
        } else {
            return false;
        }
    }

    private static function initFromData($data)
    {
        $data = explode('A', $data);
        self::$session = [
            'group_id' => $data[2],
            'user_id' => $data[0],
            'app_id' => $data[1]
        ];
    }
}