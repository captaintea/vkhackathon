<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Group
 *
 * @property integer $id
 * @property string $name
 * @property string $rss
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property integer $vk_group_id
 * @method static \Illuminate\Database\Query\Builder|\App\Group whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Group whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Group whereRss($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Group whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Group whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Group whereVkGroupId($value)
 * @mixin \Eloquent
 */
class Group extends Model
{
	public $appends = [
		'active'
	];

	protected $fillable = [
		'id', 'name', 'rss', 'vk_group_id'
	];

	public function users() {
		return $this->belongsToMany('App\User', 'users_groups');
	}

	public function setActiveAttribute($active) {
		$this->attributes['active'] = $active;
	}

	public function getActiveAttribute($value) {
		return $value;
	}

	public function toArray()
	{
		$array = parent::toArray();
		if (isset($this->attributes['active'])) {
			$array['active'] = $this->attributes['active'];
		}
		return $array;
	}
}
