<?php

// Laravel
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembershipGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('membership_group', function(Blueprint $table) {
            $table->increments('id');

            $table->integer('hub_id')->unsigned();

            $table->double('multiplier', 4, 2)->unsigned()->default(1.0);
            $table->string('name', 120);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('membership_group');
    }
}
