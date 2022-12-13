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
        Schema::table('invoices', function (Blueprint $table) {
            $table->index('company_id');
            $table->foreign('company_id')
            ->references('id')->on('companies')->onDelete('cascade');

            $table->index('congregation_id');
            $table->foreign('congregation_id')
            ->references('id')->on('people')->onDelete('cascade');

            $table->index('agent_id');
            $table->foreign('agent_id')
            ->references('id')->on('people')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id']);

            $table->dropForeign(['congregation_id']);
            $table->dropIndex(['congregation_id']);

            $table->dropForeign(['agent_id']);
            $table->dropIndex(['agent_id']);
        });
    }
};
