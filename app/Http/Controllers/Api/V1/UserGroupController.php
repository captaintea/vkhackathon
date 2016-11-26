<?php

namespace App\Http\Controllers\Api\V1;

use App\Group;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class UserGroupController extends Controller
{
    public function getGroups(Request $request)
	{
    	$validator = \Validator::make($request->all(), [
    		'user_id' => 'required',
			'vk_group_id' => 'required|exists:vk_groups,vk_group_id'
		]);
		if ($validator->fails()) {
			return $this->getErrorResponse($validator->errors(), 403);
		}
		$activeGroups = Group::where('vk_group_id', $request->get('vk_group_id'))
			->with('users')->whereHas('users', function($q) use($request) {
				$q->where('users.id', $request->get('user_id'));
			})->get();
		$allGroups = Group::where('vk_group_id', $request->get('vk_group_id'))
			->orderBy('id', 'DESC')->get();
		foreach ($allGroups as &$group) {
			if (!empty($activeGroups->where('id', $group->id)->first())) {
				$group->active = true;
			} else {
				$group->active = false;
			}
		}
		return $this->getSuccessResponse($allGroups);
	}

	public function updateGroups(Request $request)
	{
		$validator = \Validator::make($request->all(), [
			'user_id' => 'required',
			'group_ids' => 'array'
		]);
		//todo хорощо бы еще группу проверить...
		if ($validator->fails()) {
			return $this->getErrorResponse($validator->errors(), 403);
		}
		$groupIds = $request->get('group_ids', []);
		$userId = $request->get('user_id');
		$user = User::find($request->get('user_id'));
		if (empty($user)) {
			User::insertIgnore([$userId]);
		}
		$sync = $this->prepareSyncArray($groupIds, $userId);
		if (is_array($user->groups()->sync($sync))) {
			return $this->getSuccessResponse(1);
		} else {
			return $this->getErrorResponse('Server error', 500);
		}
	}

	private function prepareSyncArray(array $groupIds, $userId)
	{
		$groups = Group::whereIn('id', $groupIds)->get();
		$sync = [];
		foreach ($groupIds as $groupId) {
			if (!empty($groups->where('id', $groupId)->first())) {
				$sync[$groupId]['user_id'] = $userId;
			}
		}
		return $sync;
	}
}
