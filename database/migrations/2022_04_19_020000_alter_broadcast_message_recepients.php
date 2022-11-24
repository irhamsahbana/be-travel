<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('broadcast_message_recepients', function (Blueprint $table) {
            $table->index('broadcast_message_id');
            $table->foreign('broadcast_message_id')->references('id')->on('broadcast_messages')->onDelete('cascade');

            $table->index('person_id');
            $table->foreign('person_id')->references('id')->on('people')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('broadcast_message_recepients', function (Blueprint $table) {
            $table->dropForeign(['broadcast_message_id']);
            $table->dropIndex(['broadcast_message_id']);

            $table->dropForeign(['person_id']);
            $table->dropIndex(['person_id']);
        });
    }
};
