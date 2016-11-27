<?php


namespace App\Providers\Vk;


class VkException extends \Exception
{
    protected $raw = '';

    public function setRaw($data)
    {
        $this->raw = $data;
        return $this;
    }
    
    public function getRaw() {
        return $this->raw;
    }
}