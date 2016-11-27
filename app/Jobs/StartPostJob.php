<?php

namespace App\Jobs;

use App\Messaging;
use App\VkGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StartPostJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $postId;

    public function __construct($postId)
    {
        $this->postId = $postId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $post = Messaging::find($this->postId);
        if ($post instanceof Messaging && !$post->is_sent) {
            $post->is_sent = true;
            $post->save();

            $vGroup = VkGroup::find($post->vk_group_id);
            
            if (!($vGroup instanceof VkGroup) || !$vGroup->vk_group_token) {
                \Log::warning("Group #".$post->vk_group_id.' not found or no token', [
                    'group' => !!$vGroup 
                ]);
                return;
            }
            
            $token = $vGroup->vk_group_token;
            
            $groupIds = $post->groups()->get()->pluck('id')->all();
            \Log::debug('Found ' . count($groupIds), [
                'ids' => $groupIds
            ]);

            \DB::table('users_groups')
                ->select('user_id')
                ->whereIn('group_id', $groupIds)
                ->chunk(25, function ($uids) use ($post, $token) {
                    $ids = $uids->pluck('user_id')->all();
                    $message = $post->message;
                    
                    dispatch( new SendMessageJob($ids, $message, $token, 0) );
                });
        } else {
            \Log::warning("Post not found or already sent", [
                'id' => $this->postId,
                'exist' => !!$post
            ]);
        }
    }
}
