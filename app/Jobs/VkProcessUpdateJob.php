<?php

namespace App\Jobs;

use App\Messaging;
use App\Providers\Vk\VkException;
use App\VkGroup;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class VkProcessUpdateJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->request['type'] == 'message_new') {
            $message = $this->request['object'];
            $groupId = (int)$this->request['group_id'];
            $userId = $message['user_id'];
            $group = VkGroup::find($groupId);
            if ($group instanceof VkGroup) {
                $this->processMessage($message, $group, (int)$userId);
            }
        }
    }

    private function processMessage($message, VkGroup $group, $userId)
    {
        $params = $this->copyMessage($message);

        $cacheKey = 'waitFor.'.$userId;
        $isWaitForUser = \Cache::get($cacheKey);

        if ($isWaitForUser) {
            \Cache::pull($cacheKey);
            
            \Log::info('Start post ', ['user_id', $userId]);

            $listIds = explode(',', $isWaitForUser);
            $listIds = array_map( function ($id) { return (int)$id; }, $listIds );

            $data = [
                'message' => $params,
                'vk_group_id' => $group->vk_group_id
            ];
            $messaging = Messaging::create($data);
            Messaging::insertExceptNotExistedGroups($listIds, $messaging->id);
            dispatch(new StartPostJob($messaging->id));
        } else {

        }
    }

    protected function copyMessage($message)
    {
        $parameters = [];
        unset($parameters['user_id']);
        unset($parameters['user_ids']);
        unset($parameters['domain']);
        unset($parameters['chat_id']);

        $parameters['message'] = isset($message['body']) ? $message['body'] : '';

        if (isset($message['geo'])) {
            $parameters['lat'] = isset($message['geo']['place']['latitude']) ? $message['geo']['place']['latitude'] : 0;
            $parameters['long'] = isset($message['geo']['place']['latitude']) ? $message['geo']['place']['longitude'] : 0;
            if (!empty($message['geo']['coordinates'])) {
                $cord = $message['geo']['coordinates'];
                $cord = explode(' ', $cord);
                $parameters['lat'] = $cord[0];
                $parameters['long'] = $cord[1];
            }
        }

        if (isset($message['fwd_messages'])) {
            $parameters['message'] = "[Пересланное сообщение]\n".$parameters['message'];
        }

        $attachments = isset($message['attachments']) ? $message['attachments'] : [];
        $attachIds = [];
        foreach ($attachments as $attach) {
            $type = $attach['type'];
            $id = $type;
            $attach = $attach[$type];
            if (isset($attach['id'], $attach['owner_id'])) {
                $id .= $attach['owner_id'] . '_' . $attach['id'];
                if (isset($attach['access_key'])) {
                    $id .= '_' . $attach['access_key'];
                    if ($type == 'photo') {
                        $max = 0;
                        foreach ($attach as $key => $value) {
                            if (mb_strpos($key, 'photo') === 0) {
                                $x = strtr($key, ['photo_'=>'']);
                                if ($x > $max) {
                                    $max = $x;
                                }
                            }
                        }
                        if ($max) {
                            $url = $attach['photo_'.$max];
                            $parameters['message'] .= ' '.$url;
                        }
                    }
                }
                $attachIds[] = $id;
            } else if ($type == 'wall') {
                $attachIds[] = 'wall' . $attach['to_id'] . '_' . $attach['id'];
            } else if ($type == 'sticker') {
                $parameters['message'] .= ' [стикер] ' . $attach['photo_352'];
            } else if ($type == 'link') {
                $parameters['message'] .= ' ' . $attach['url'];
            } else if ($type == 'gift') {
                $parameters['message'] .= ' ' . $attach['thumb_256'];
            }
        }
        if (!empty($attachIds)) {
            $parameters['attachment'] = implode(',', $attachIds);
        }

        return $parameters;
    }
}
