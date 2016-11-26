<?php

namespace App\Http\Controllers\Api\V1;

use App\Group;
use App\Messaging;
use App\Vk\Auth;
use App\VkGroup;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class MessagingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		return $this->getSuccessResponse(Messaging::all()->take(self::ROW_LIMIT));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		$validator = $this->validateInsertRequest($request);
		if ($validator->fails()) {
			return $this->getErrorResponse($validator->errors(), 403);
		}
		$messagingData = $request->all();
		$messagingData['vk_group_id'] = Auth::groupId();
		DB::beginTransaction();
		$vkGroup = VkGroup::createIfNotExist($messagingData['vk_group_id']);
		if (empty($vkGroup)) {
			DB::rollBack();
			return $this->getErrorResponse('Server error', 500);
		}
        $messaging = Messaging::create($messagingData);
		if (!empty($messaging)) {
			if (!empty($messagingData['group_id'])) {
				Messaging::insertExceptNotExistedGroups($messagingData['group_id'], $messaging->id);
			}
			DB::commit();
			return $this->getSuccessResponse($messaging);
		} else {
			DB::rollBack();
			return $this->getErrorResponse('Server error', 500);
		}
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
		$validator = $this->validateUpdateRequest($request);
		if ($validator->fails()) {
			return $this->getErrorResponse($validator->errors(), 403);
		}
		$messaging = Messaging::find($id);
		if (empty($messaging)) {
			return $this->getErrorResponse('Messaging is not found', 404);
		}
		$messagingData = $request->all();
		if (isset($messagingData['vk_group_id'])) {
			unset($messagingData['vk_group_id']);
		}
		DB::beginTransaction();
		if ($messaging->update($messagingData)) {
			$sync = $this->prepareSyncArray($messagingData['group_id'], $id);
			$messaging->groups()->sync($sync);
			DB::commit();
			return $this->getSuccessResponse(1);
		} else {
			return $this->getErrorResponse('Server error', 500);
		}

    }

    private function prepareSyncArray(array $groupIds, $messagingId)
	{
		$groups = Group::whereIn('id', $groupIds)->get();
    	$sync = [];
		foreach ($groupIds as $groupId) {
			if (!empty($groups->where('id', $groupId)->first())) {
				$sync[$groupId]['messaging_id'] = $messagingId;
			}
		}
		return $sync;
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
		if (!empty(Messaging::destroy($id))) {
			return $this->getSuccessResponse(1);
		} else {
			return $this->getErrorResponse('Messaging is not found', 404);
		}
    }

	private function validateInsertRequest(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'message' => 'required|array',
			'group_id' => 'array'
		]);
		return $validator;
	}

	private function validateUpdateRequest(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'message' => 'array',
			'group_id' => 'array',
			'is_sent' => 'boolean'
		]);
		return $validator;
	}
}
