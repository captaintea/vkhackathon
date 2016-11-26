<?php


namespace App\Vk;


class VkExecutor
{

    const V = '5.53';

    public function execute(VkApiRequest $request) : VkApiResponse
    {

        $params = $request->getParams();
        $params = $this->applyDefaultParams($params);
        $data = http_build_query($params);

        $opts = ['http' =>
            [
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $data,
                'timeout' => 31,
                'ignore_errors' => true
            ]
        ];

        $context  = stream_context_create($opts);
        try {
            $result = file_get_contents('https://api.vk.com/method/'.$request->getMethod(), false, $context);
        } catch (\Exception $e) {
            $result = '';
        }
        
        $json = json_decode($result, true);
        $response = new VkApiResponse($request);
        if ($json) {
            if (isset($json['response'])) {
                $response->setCode(200);
                $response->setResponse($json['response']);
                $response->setFullResponse($json);
            } elseif (isset($json['error'])) {
                $code = $json['error']['error_code'];
                $message = $json['error']['error_msg'];
                $response->setCode((int)$code);
                $response->setMessage($message);
                $response->setFullResponse($json);
            } else {
                $response->setCode(500);
                $response->setFullResponse($result);
            }
        } else {
            $response->setCode(500);
            $response->setFullResponse($result);
        }
        return $response;
    }

    private function applyDefaultParams($params)
    {
        if (!isset($params['v'])) {
            $params['v'] = self::V;
        }
        if (!isset($params['lang'])) {
            $params['lang'] = 'ru';
        }
        return $params;
    }

    public function canRetryLaterWithCode($code)
    {
        return in_array($code, [
            500,
            0,
            1,
            6,
            9,
            10,
            18
        ]);
    }
}