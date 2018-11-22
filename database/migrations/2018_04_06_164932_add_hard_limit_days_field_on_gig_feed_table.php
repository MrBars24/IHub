<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHardLimitDaysFieldOnGigFeedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gig_feed', function (Blueprint $table) {
            $table->tinyInteger('hard_limit_days')->unsigned()->default(7)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gig_feed', function (Blueprint $table) {
            $table->dropColumn('hard_limit_days');
        });
    }
}
