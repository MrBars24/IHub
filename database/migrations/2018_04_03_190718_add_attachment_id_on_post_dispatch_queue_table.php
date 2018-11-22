<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttachmentIdOnPostDispatchQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('post_dispatch_queue', function(Blueprint $table) {
            $table->integer('attachment_id')->after('post_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('post_dispatch_queue', function(Blueprint $table) {
            $table->dropColumn('attachment_id');
        });
    }
}
