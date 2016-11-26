<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Vk\Apps;
use App\Vk\Auth;
use App\Vk\Request as VkRequest;

class AuthController extends Controller
{

    public function index(Request $r)
    {
        try {
            $query = $r->get('query', "");
            $appSecret = Apps::getAppSecret();
            $vkRequest = new VkRequest($query, $appSecret);
            if ($vkRequest->isValid()) {
                if ($vkRequest->getGroupId()) {
                    if ($vkRequest->getAdminLevel() >= 4) {
                        $token = Auth::createSession($vkRequest);
                        return response()->json([
                            'response' => [
                                'token' => $token
                            ]
                        ]);
                    } else {
                        return $this->onlyForAdmins();
                    }
                } else {
                    return $this->onlyForGroup();
                }
            } else {
                return $this->invalidRequest();
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'code' => 500,
                    'description' => 'Service unavailable'
                ]
            ], 500);
        }
    }

    private function invalidRequest()
    {
        return response()->json([
        'error' => [
            'code' => 403,
            'description' => 'Invalid request'
        ]
    ], 403);
    }

    private function onlyForGroup()
    {
        return response()->json([
            'error' => [
                'code' => 403,
                'description' => 'App can run only in group'
            ]
        ], 403);
    }

    private function onlyForAdmins()
    {
        return response()->json([
            'error' => [
                'code' => 403,
                'description' => 'App only for admins'
            ]
        ], 403);
    }

}