<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogAggregatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_aggregates', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('log_time')->index();
            $table->string('log_type')->index();
            $table->string('hash')->unique();
            $table->text('data');
            $table->timestamps();
        });
        
        Schema::table('emails', function(Blueprint $table) {
            $table->text('related_logs')->nullable();
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('log_aggregates');
        Schema::table('emails', function(Blueprint $table) {
            $table->dropColumn('related_logs');
            $table->dropIndex('emails_updated_at_index');
        });
    }
}
