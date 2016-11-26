<?php

namespace App\Vk;


class Request
{
    protected $query;
    protected $data;
    protected $appSecret;
    protected $isValid = false;

    /**
     * Validator constructor.
     * @param $query
     * @param $appSecret
     */
    public function __construct($query, $appSecret)
    {
        $this->query = preg_replace('/^\?/usi', '', $query);
        parse_str($this->query, $this->data);
        $this->appSecret = $appSecret;
        $this->isValid = $this->check($query, $appSecret);
    }

    protected function check($query, $secret2) {
        $query = preg_replace('/^\?/usi', '', $query);
        $params = [];
        parse_str($query, $params);
        $sign = "";

        foreach ($params as $key => $param) {
            if ($key == 'hash' || $key == 'sign') continue;
            if ($key == 'api_result') {
                $param = urlencode($param);
                $param = strtr($param, ['+'=>'%20']);
            }
            if ($key == 'ad_info') {
                $param = strtr($param, [' '=>'+']);
            }
            $sign .= ($param);
        }

        $sig = $secret2 ? hash_hmac('sha256', $sign, $secret2) : "";
        $check = $params['sign'] ?? '';
        if ($sig == $check) {
            $apiId = $this->data['api_id'];
            $viewerId = $this->data['viewer_id'];
            $authKey = $this->data['auth_key'];
            return Apps::isValidData($apiId, $viewerId, $authKey);
        }
        return false;
    }

    public function isValid() {
        return $this->isValid;
    }

    public function getUserId()
    {
        return $this->data['viewer_id'];
    }

    public function getAppId()
    {
        return $this->data['api_id'];
    }

    public function getGroupId()
    {
        return $this->data['group_id'] ?? false;
    }

    public function getAdminLevel()
    {
        return (int)( $this->data['viewer_type'] ?? 0 );
    }

    public function getUserData() {
        $api = $this->data['api_result'] ?? '{}';
        $json = json_decode($api, true);
        if ($json && isset($json['response'])) {
            $data = $json['response'];
            $data['id'] = $this->getUserId();
            return $data;
        } else {
            return [
                'id' => $this->getUserId()
            ];
        }
    }
}