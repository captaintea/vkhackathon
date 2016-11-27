<?php


namespace App\Http\Controllers;


use App\Jobs\VkProcessUpdateJob;
use Illuminate\Http\Request;

class VkController extends Controller
{
    

    public function callback(Request $r)
    {
        \Log::debug('Callback', $r->all());
        if ($r->has('type')) {
            if ($r->get('type') == 'confirmation') {
                $gId = $r->get('group_id', 0);
                $cacheKey = 'gCode'.$gId;
                $code = \Cache::get('gCode'.$gId);
                if ($code) {
                    \Log::info('Pass code key '.$cacheKey.' ['.$code.']');
                    return response($code);
                } else {
                    \Log::error('No code for a key '.$cacheKey);
                    return response('No group with id ' . $gId);
                }
            } else {
                $request = $r->all();
                dispatch(new VkProcessUpdateJob($request));
            }
        }
        return response("ok");
    }
}
