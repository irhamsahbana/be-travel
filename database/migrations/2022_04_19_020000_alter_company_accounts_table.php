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
        Schema::table('company_accounts', function (Blueprint $table) {
            $table->index('company_id');
            $table->foreign('company_id')
            ->references('id')->on('companies')->onDelete('cascade');

            $table->index('bank_id');
            $table->foreign('bank_id')
            ->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_accounts', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id']);

            $table->dropForeign(['bank_id']);
            $table->dropIndex(['bank_id']);
        });
    }
};
