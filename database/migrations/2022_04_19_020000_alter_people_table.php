<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('people', function (Blueprint $table) {
            $table->index('company_id');
            $table->foreign('company_id')
            ->references('id')->on('companies')->onDelete('cascade');

            $table->index('branch_id');
            $table->foreign('branch_id')
            ->references('id')->on('branches')->onDelete('cascade');

            $table->index('category_id');
            $table->foreign('category_id')
            ->references('id')->on('categories');

            $table->index('city_id');
            $table->foreign('city_id')
            ->references('id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id']);

            $table->dropForeign(['branch_id']);
            $table->dropIndex(['branch_id']);

            $table->dropForeign(['category_id']);
            $table->dropIndex(['category_id']);

            $table->dropForeign(['city_id']);
            $table->dropIndex(['city_id']);
        });
    }
}
