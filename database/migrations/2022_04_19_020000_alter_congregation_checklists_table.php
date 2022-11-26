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
        Schema::table('congregation_checklists', function (Blueprint $table) {
            $table->index('person_id');
            $table->foreign('person_id')
            ->references('id')->on('people')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('congregation_checklists', function (Blueprint $table) {
            $table->dropForeign(['person_id']);
            $table->dropIndex(['person_id']);
        });
    }
};
