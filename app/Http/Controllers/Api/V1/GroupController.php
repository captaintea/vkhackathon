<?php

namespace App\Http\Controllers\Api\V1;

use App\Group;
use App\Http\Controllers\Controller;
use App\User;
use App\UserGroup;
use App\Vk\Auth;
use App\VkGroup;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		$groups = Group::where('vk_group_id', Auth::groupId())->take(self::ROW_LIMIT)
			->orderBy('id', 'DESC')->get();
        return $this->getSuccessResponse($groups);
    }

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
    public function store(Request $request)
    {
    	$validator = $this->validateInsertRequest($request);
		if ($validator->fails()) {
			return $this->getErrorResponse($validator->errors(), 403);
		}
		$groupData = $request->all();
		DB::beginTransaction();
		$vkGroupId = Auth::groupId();
		$vkGroup = VkGroup::createIfNotExist($vkGroupId);
		if (empty($vkGroup)) {
			DB::rollBack();
			return $this->getErrorResponse('Server error', 500);
		}
		$groupData['vk_group_id'] = $vkGroupId;
        $group = Group::create($groupData);
		if (empty($group)) {
			DB::rollBack();
			return $this->getErrorResponse('Server error', 500);
		}
		DB::commit();
		return new JsonResponse(['response' => $group]);
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
		$group = Group::find($id);
		if (empty($group)) {
			return $this->getErrorResponse('Group is not found', 404);
		}
		$groupData = $request->all();
		if (isset($groupData['vk_group_id'])) {
			unset($groupData['vk_group_id']);
		}
		DB::beginTransaction();
		if($group->update($groupData)) {
			if (!empty($groupData['to_add'])) {
				User::insertIgnore($groupData['to_add']);
				UserGroup::insertIgnore($groupData['to_add'], $id);
			}
			if (!empty($groupData['to_delete'])) {
				UserGroup::deleteUsers($groupData['to_delete'], $id);
			}
			DB::commit();
			return $this->getSuccessResponse(1);
		} else {
			DB::rollBack();
			return $this->getErrorResponse('Server error', 500);
		}
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    	if (!empty(Group::destroy($id))) {
    		return $this->getSuccessResponse(1);
		} else {
			return $this->getErrorResponse('Group is not found', 404);
		}
    }

	/**
	 * @param Request $request
	 * @return \Illuminate\Validation\Validator
	 */
	private function validateUpdateRequest(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'name' => 'string|max:99',
			'rss' => 'string|max:99',
			'to_add' => 'array',
			'to_delete' => 'array'
		]);
		return $validator;
	}

	private function validateInsertRequest(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'name' => 'required|string|max:99',
			'rss' => 'string|max:99',
			'to_add' => 'array',
			'to_delete' => 'array'
		]);
		return $validator;
	}
}
