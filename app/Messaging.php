<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Messaging extends Model
{
    protected $table = 'messaging';

	protected $fillable = ['name', 'message', 'vk_group_id'];

	public static function insertExceptNotExistedGroups(array $groupIds, $messagingId)
	{
		$groups = Group::whereIn('id', $groupIds)->get();
		foreach ($groupIds as $groupId) {
			if (!empty($groups->where('id', $groupId)->first())) {
				MessagingGroup::create([
					'messaging_id' => $messagingId,
					'group_id' => $groupId
				]);
			}
		}
	}

	public function groups() {
		return $this->belongsToMany('App\Group', 'messaging_groups');
	}
}
