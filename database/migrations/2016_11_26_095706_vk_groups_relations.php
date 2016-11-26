<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class VkGroupsRelations extends Migration
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
            $table->integer('vk_group_id')->unique()->unsigned();

            $table->foreign('vk_group_id')->references('vk_group_id')->on('vk_groups')
                ->onUpdate('cascade')->onInsert('restrict')->onDelete('restrict');
        });

        Schema::table('messaging', function($table)
        {
            $table->integer('vk_group_id')->unique()->unsigned();

            $table->foreign('vk_group_id')->references('vk_group_id')->on('vk_groups')
                ->onUpdate('cascade')->onInsert('restrict')->onDelete('restrict');
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
            $table->dropForeign('messaging_vk_group_id_foreign');
            $table->dropColumn('vk_group_id');
        });

        Schema::table('messaging', function($table)
        {
            $table->dropForeign('messaging_vk_group_id_foreign');
            $table->dropColumn('vk_group_id');
        });
    }
}
