<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateVkGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vk_groups', function($table)
        {
            $table->integer('vk_group_id')->unique()->unsigned();
            $table->string('vk_group_token', 250);

            $table->timestamps();
            $table->softDeletes();

            $table->primary('vk_group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('vk_groups');
    }
}
