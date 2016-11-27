<?php

namespace App\Jobs;

use App\Providers\Vk\Core;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class InstallCallbackApi implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $groupId;
    protected $token;

    public function __construct($groupId, $token)
    {
        $this->groupId = $groupId;
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $vkCore = new Core('');
        try {
            $code = $vkCore->api('groups.getCallbackConfirmationCode', ['group_id' => $this->groupId], $this->token);
            $code = $code['response']['code'];
            \Cache::put('gCode'.$this->groupId, $code, 20);
            \Log::debug("Install callback api 1", ['r' => $code, 'group_id'=>$this->groupId]);
            $vkCore->api('groups.setCallbackSettings', [
                'message_new' => '1',
                'group_id' => $this->groupId
            ], $this->token);
            $vkCore->api('groups.setCallbackServer', [
                'group_id' => $this->groupId,
                'server_url' => 'https://hsvk16.tk/vk-callback'
            ], $this->token);
        } catch (\Exception $e) {
            \Log::error($e);
        }
    }
}
