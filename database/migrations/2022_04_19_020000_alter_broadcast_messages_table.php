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
        Schema::table('broadcast_messages', function (Blueprint $table) {
            $table->index('company_id');
            $table->foreign('company_id')
            ->references('id')->on('companies')->onDelete('cascade');

            $table->index('person_id');
            $table->foreign('person_id')
            ->references('id')->on('people')->onDelete('cascade');

            $table->index(['scheduled_date', 'scheduled_time'], 'scheduled_date_time_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('broadcast_messages', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id']);

            $table->dropForeign(['person_id']);
            $table->dropIndex(['person_id']);

            $table->dropIndex('scheduled_date_time_index');
        });
    }
};
