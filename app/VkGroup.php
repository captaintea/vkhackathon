<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VkGroup extends Model
{
    protected $table = 'vk_groups';

	protected $fillable = ['vk_group_id', 'token'];

	public $primaryKey = 'vk_group_id';

	public $incrementing = false;
}
