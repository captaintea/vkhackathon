<?php

namespace App\Http\Controllers\Api\V1;

use App\Jobs\InstallCallbackApi;
use App\Vk\Auth;
use App\VkGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class FastBroadcastController extends Controller
{
    
    public function store(Request $request)
    {
		$groupIds = $request->get('group_ids', []);
		$userId = Auth::userId();

		$cacheKey = 'waitFor.'.$userId;
		
		\Cache::put($cacheKey, implode(',',$groupIds), 60);
		
		return $this->getSuccessResponse(1);
    }
}
