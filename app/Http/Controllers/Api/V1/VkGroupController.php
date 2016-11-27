<?php

namespace App\Http\Controllers\Api\V1;

use App\Jobs\InstallCallbackApi;
use App\VkGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class VkGroupController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $vkGroup = VkGroup::createIfNotExist($id);
		if ($vkGroup) {
			return $this->getSuccessResponse($vkGroup);
		} else {
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
		$vkGroup = VkGroup::find($id);
		if (empty($vkGroup)) {
			return $this->getErrorResponse('Vk group is not found', 404);
		}
		if ($vkGroup->update($request->all())) {
			if ($vkGroup->vk_group_token) {
				dispatch(new InstallCallbackApi($id, $vkGroup->vk_group_token));
			}
			return $this->getSuccessResponse(1);
		} else {
			return $this->getErrorResponse('Server error', 500);
		}
    }

    public function validateUpdateRequest(Request $request) {
		$validator = Validator::make($request->all(), [
			'vk_group_id' => 'integer|min:1',
			'vk_group_token' => 'string'
		]);
		return $validator;
	}
}
