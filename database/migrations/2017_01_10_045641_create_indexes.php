<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('target_lists', function (Blueprint $table) {
            $table->unique('name');
        });
        Schema::table('target_list_target_user', function (Blueprint $table) {
            $table->index('target_user_id');
            $table->index('target_list_id');
        });
        Schema::table('campaigns', function (Blueprint $table) {
            $table->index('name');
            $table->index('target_list_id');
        });
        Schema::table('emails', function (Blueprint $table) {
            $table->index('campaign_id');
            $table->index('sender_name');
            $table->index('sender_email');
            $table->index('receiver_name');
            $table->index('receiver_email');
            $table->index('subject');
            $table->index('status');
            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('target_lists', function (Blueprint $table) {
            $table->dropUnique('target_lists_name_unique');
        });
        Schema::table('target_list_target_user', function (Blueprint $table) {
            $table->dropIndex('target_list_target_user_target_user_id_index');
            $table->dropIndex('target_list_target_user_target_list_id_index');
        });
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropIndex('campaigns_name_index');
            $table->dropIndex('campaigns_target_list_id_index');
        });
        Schema::table('emails', function (Blueprint $table) {
            $table->dropIndex('emails_campaign_id_index');
            $table->dropIndex('emails_sender_name_index');
            $table->dropIndex('emails_sender_email_index');
            $table->dropIndex('emails_receiver_name_index');
            $table->dropIndex('emails_receiver_email_index');
            $table->dropIndex('emails_subject_index');
            $table->dropIndex('emails_status_index');
            $table->dropIndex('emails_uuid_index');
        });
    }
}
