<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
	protected $fillable = [
		'id', 'name', 'rss', 'vk_group_id'
	];
}
