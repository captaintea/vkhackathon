<?php

namespace App\Http\Controllers\Api\V1;

use App\Group;
use App\Http\Controllers\Controller;
use App\VkGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class GroupController extends Controller
{
	const ROW_LIMIT = 100;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->getSuccessResponse(Group::all(self::ROW_LIMIT));
    }

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
    public function store(Request $request)
    {
    	$validator = $this->validateRequest($request);
		if ($validator->fails()) {
			return $this->getErrorResponse($validator->errors(), 403);
		}
		$groupData = $request->all();
		$vkGroup = VkGroup::where('vk_group_id', $groupData['vk_group_id'])->first();
		if (empty($vkGroup)) {
			$vkGroup = VkGroup::create([
				'vk_group_id' => $groupData['vk_group_id'],
				'token' => ''
			]);
			if (empty($vkGroup)) {
				return $this->getErrorResponse('Server error', 500);
			}
		}
        $group = Group::create($groupData);
		if (empty($group)) {
			return $this->getErrorResponse('Server error', 500);
		}
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
		$validator = $this->validateRequest($request);
		if ($validator->fails()) {
			return $this->getErrorResponse($validator->errors(), 403);
		}
		$group = Group::find($id);
		if (empty($group)) {
			return $this->getErrorResponse('Group is not found', 404);
		}
		if($group->update($request->all())) {
			return $this->getSuccessResponse(true);
		} else {
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
    		return $this->getSuccessResponse(true);
		} else {
			return $this->getErrorResponse('Group is not found', 404);
		}
    }

	/**
	 * @param Request $request
	 * @return \Illuminate\Validation\Validator
	 */
	private function validateRequest(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'name' => 'required|string|max:99',
			'rss' => 'required|string|max:99',
			'vk_group_id' => 'required|int|min:1'
		]);
		return $validator;
	}
}
