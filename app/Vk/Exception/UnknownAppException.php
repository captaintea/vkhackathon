<?php


namespace App\Vk\Exception;


class UnknownAppException extends VkException
{

    public function __construct(int $appId)
    {
        parent::__construct('Unknown application id :'.$appId);
    }
}