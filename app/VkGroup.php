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

	protected $fillable = ['vk_group_id', 'token'];

	public $primaryKey = 'vk_group_id';

	public $incrementing = false;
}
