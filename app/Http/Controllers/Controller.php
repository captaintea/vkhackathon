<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	const ROW_LIMIT = 100;

	protected function getErrorResponse($description, $code, $httpCode = false) {
		return new JsonResponse([
			'error' => [
				'code' => $code,
				'description' => $description
			]
		], $httpCode ? $httpCode : $code);
	}

	protected function getSuccessResponse($data, $code = 200) {
		return new JsonResponse(['response' => $data]);
	}

}
