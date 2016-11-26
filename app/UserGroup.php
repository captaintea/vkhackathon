<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\UserGroup
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $group_id
 * @property boolean $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @method static \Illuminate\Database\Query\Builder|\App\UserGroup whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserGroup whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserGroup whereGroupId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserGroup whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserGroup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserGroup whereDeletedAt($value)
 * @mixin \Eloquent
 */
class UserGroup extends Model
{
    protected $table = 'users_groups';

	protected $fillable = ['user_id', 'group_id'];

	public static function insertIgnore(array $ids, $groupId)
	{
		$userGroups = UserGroup::whereIn('user_id', $ids)->where('group_id', $groupId)->get();
		foreach ($ids as $id) {
			if (empty($userGroups->where('user_id', $id)->first())) {
				UserGroup::create([
					'user_id' => $id,
					'group_id' => $groupId
				]);
			}
		}
	}

	public static function deleteUsers(array $ids, $groupId)
	{
		UserGroup::whereIn('user_id', $ids)->where('group_id', $groupId)->delete();
	}
}
