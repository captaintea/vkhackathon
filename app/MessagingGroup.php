<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MessagingGroup extends Model
{
    protected $table = 'messaging_groups';

	protected $fillable = ['messaging_id', 'group_id'];
}
