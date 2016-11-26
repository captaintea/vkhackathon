<?php


namespace App\Vk\Exception;


class VkValidateException extends VkException
{
    protected $bugs = [];

    public function __construct(array $bugs, $code)
    {
        parent::__construct("Invalid params", $code);
        $this->bugs = $bugs;
    }

    public function getBugs() {
        return $this->bugs;
    }
}