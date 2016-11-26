<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\VkGroup
 *
 * @property integer $vk_group_id
 * @property string $token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @method static \Illuminate\Database\Query\Builder|\App\VkGroup whereVkGroupId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\VkGroup whereToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\VkGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\VkGroup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\VkGroup whereDeletedAt($value)
 * @mixin \Eloquent
 */
class VkGroup extends Model
{
    protected $table = 'vk_groups';

	protected $fillable = ['vk_group_id', 'vk_group_token'];

	public $primaryKey = 'vk_group_id';

	public $incrementing = false;

	public static function createIfNotExist($vkGroupId) {
		$vkGroup = VkGroup::where('vk_group_id', $vkGroupId)->first();
		if (empty($vkGroup)) {
			$vkGroup = VkGroup::create([
				'vk_group_id' => $vkGroupId,
				'vk_group_token' => ''
			]);
			return $vkGroup;
		} else {
			return $vkGroup;
		}
	}
}
