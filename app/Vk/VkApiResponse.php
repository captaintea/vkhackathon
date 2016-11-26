<?php


namespace App\Vk;


class VkApiResponse
{
    protected $request;
    protected $response = [];
    protected $fullResponse = '';
    protected $message = '';
    protected $code = 0;

    public function __construct(VkApiRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @return VkApiRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param VkApiRequest $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getFullResponse()
    {
        return $this->fullResponse;
    }

    /**
     * @param mixed $fullResponse
     */
    public function setFullResponse($fullResponse)
    {
        $this->fullResponse = $fullResponse;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function isSuccess()
    {
        return $this->code == 200;
    }


}