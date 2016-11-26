<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class MessagingGroupsRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messaging_groups', function($table)
        {
            $table->increments('id');
            $table->integer('messaging_id')->unsigned();
            $table->integer('group_id')->unsigned();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('messaging_id')->references('id')->on('messaging')
                ->onUpdate('cascade')->onInsert('restrict')->onDelete('restrict');
            $table->foreign('group_id')->references('id')->on('groups')
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
        Schema::drop('messaging_groups');
    }
}
