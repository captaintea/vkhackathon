<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class VkGroupId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('groups', function($table)
        {
            $table->integer('vk_group_id');
        });

        Schema::table('messaging', function($table)
        {
            $table->integer('vk_group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('groups', function($table)
        {
            $table->dropColumn('vk_group_id');
        });

        Schema::table('messaging', function($table)
        {
            $table->dropColumn('vk_group_id');
        });
    }
}
