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
            \Cache::put('gCode' . $this->groupId, $code, 20);
            \Log::debug("Install callback api 1", ['r' => $code, 'group_id', $this->groupId]);
            $r = $vkCore->api('groups.setCallbackSettings', [
                'message_new' => '1',
                'photo_new' => '0',
                'audio_new' => '0',
                'video_new' => '0',
                'wall_reply_new' => '0',
                'wall_reply_edit' => '0',
                'wall_post_new' => '0',
                'board_post_new' => '0',
                'board_post_edit' => '0',
                'board_post_restore' => '0',
                'board_post_delete' => '0',
                'photo_comment_new' => '0',
                'video_comment_new' => '0',
                'market_comment_new' => '0',
                'group_join' => '0',
                'group_leave' => '0',
                'group_id' => $this->groupId
            ], $this->token);
            \Log::debug("Install callback api 1", ['r' => $r]);
            sleep(1);
            $result = $vkCore->api('groups.setCallbackServer', [
                'group_id' => $this->groupId,
                'server_url' => 'https://hsvk16.tk/callback'
            ], $this->token);
            \Log::debug("Install callback api 2", ['r' => $result]);
        } catch (\Exception $e) {
            \Log::error($e);
        }
    }
}
