<?php


namespace App\Http\Controllers;


use App\Jobs\VkProcessUpdateJob;
use Illuminate\Http\Request;

class VkController extends Controller
{
    

    public function callback(Request $r)
    {
        if ($r->has('type')) {
            if ($r->get('type') == 'confirmation') {
                $gId = $r->get('group_id', 0);
                $code = \Cache::get('gCode'.$gId);
                if ($code) {
                    return response($code);
                } else {
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
