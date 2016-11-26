<?php

namespace App\Http\Middleware;

use Closure;
use App\Vk\Auth;

class VkSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->has('token')) {
            return $this->noTokenResponse();
        }
        $token = $request->get('token', '');
        if (!Auth::init($token)) {
            return $this->invalidTokenResponse();
        }
        if (Auth::tooManyRequests($token, 70)) {
            return $this->tooManyRequests();
        } else {
            Auth::hit($token);
        }
        return $next($request);
    }

    public function noTokenResponse() {
        return response()->json([
            'error' => [
                'code' => 400,
                'description' => 'No token passed'
            ]
        ],400);
    }

    public function invalidTokenResponse() {
        return response()->json([
            'error' => [
                'code' => 403,
                'description' => 'Invalid token'
            ]
        ],403);
    }

    private function tooManyRequests()
    {
        return response()->json([
            'error' => [
                'code' => 429,
                'description' => 'Too many requests per minute'
            ]
        ],429);
    }
}
