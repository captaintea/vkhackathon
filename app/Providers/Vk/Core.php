<?php

namespace App\Providers\Vk;

class Core
{
    protected $appId;
    protected $secretKey;
    protected $redirectUrl = '';
    protected $v = '5.60';

    public function __construct($redirectUrl, $appId = '5592050', $secretKey = 'gBabZcHbXtQdrBMWHgl2', $v = '5.60')
    {
        $this->appId = $appId;
        $this->secretKey = $secretKey;
        $this->redirectUrl = $redirectUrl;
        $this->v = $v;
    }


    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    public function getAuthUrl($state = '')
    {
        $url = 'https://oauth.vk.com/authorize';
        $params = array(
            'client_id' => $this->getAppId(),
            'redirect_uri' => $this->getRedirectUrl(),
            'scope' => 'groups',
            'response_type' => 'code',
            'v' => $this->getVersion(),
            'state' => $state
        );
        return $url . '?' . http_build_query($params);
    }

    private function getAppId()
    {
        return $this->appId;
    }

    private function getVersion()
    {
        return '5.60';
    }

    public function getTokenByCode($code)
    {
        if (!$code) {
            throw new VkException('Code not set');
        }

        $url = 'https://oauth.vk.com/access_token';

        $params = [
            'client_id' => $this->getAppId(),
            'client_secret' => $this->getAppSecret(),
            'redirect_uri' => $this->getRedirectUrl(),
            'code' => $code
        ];

        $url .= '?' . http_build_query($params);

        $context = stream_context_create(array(
            'http' => array(
                'timeout' => 40,
                'ignore_errors' => true
            )
        ));
        try {
            $data = file_get_contents($url, null, $context);
            if ($data) {
                $json = json_decode($data, true);
                if ($json) {
                    if (isset($json['access_token'])) {
                        return $json;
                    } else {
                        throw new VkException('Fatal error then auth');
                    }
                } else {
                    throw new VkException('Cant decode data from vk ' . $data);
                }
            } else {
                throw new VkException('Cant fetch data from vk');
            }
        } catch (\Exception $e) {
            throw new VkException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function getAppSecret()
    {
        return $this->secretKey;
    }

    public function api($method, $params, $token = false)
    {
        $url = 'https://api.vk.com/method/' . $method;
        $p = [
            'v' => '5.60',
            'lang' => 'ru'
        ];
        if ($token) {
            $p['access_token'] = $token;
        }
        $p = array_merge($p, $params);
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($p));
            $data = curl_exec($ch);
            curl_close($ch);
            if ($data) {
                $json = json_decode($data, true);
                if ($json) {
                    if (isset($json['response'])) {
                        return $json;
                    } else {
                        throw (new VkException('Fatal error then request ' . $data))->setRaw($data);
                    }
                } else {
                    throw new VkException('Cant decode data from vk ' . $data);
                }
            } else {
                throw new VkException('Cant fetch data from vk');
            }
        } catch (\Exception $e) {
            throw new VkException($e->getMessage(), $e->getCode(), $e);
        }
        return false;
    }

    public function getGroupAuthUrl($params)
    {
        $url = 'https://oauth.vk.com/authorize';
        $p = array(
            'client_id' => $this->getAppId(),
            'redirect_uri' => $this->getRedirectUrl(),
            'response_type' => 'code',
            'v' => $this->getVersion(),
        );
        $p = array_merge($p, $params);
        return $url . '?' . http_build_query($p);
    }

    public function getGroupToken($code)
    {
        if (!$code) {
            throw new VkException('Code not set');
        }

        $url = 'https://oauth.vk.com/access_token';

        $params = [
            'client_id' => $this->getAppId(),
            'client_secret' => $this->getAppSecret(),
            'redirect_uri' => $this->getRedirectUrl(),
            'code' => $code
        ];

        $url .= '?' . http_build_query($params);

        $context = stream_context_create(array(
            'http' => array(
                'timeout' => 40,
                'ignore_errors' => true
            )
        ));
        try {
            $data = file_get_contents($url, null, $context);
            if ($data) {
                $json = json_decode($data, true);
                if ($json) {
                    if (isset($json['expires_in'])) {
                        return $json;
                    } else {
                        throw new VkException('Fatal error then request');
                    }
                } else {
                    throw new VkException('Cant decode data from vk ' . $data);
                }
            } else {
                throw new VkException('Cant fetch data from vk');
            }
        } catch (\Exception $e) {
            throw new VkException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
