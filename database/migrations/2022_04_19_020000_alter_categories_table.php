<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->index('category_id');
            $table->foreign('category_id')
            ->references('id')->on('categories');

            $table->index('company_id');
            $table->foreign('company_id')
            ->references('id')->on('companies');

            $table->unique(['company_id', 'name', 'group_by']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'name', 'group_by']);

            $table->dropForeign(['category_id']);
            $table->dropIndex(['category_id']);
        });
    }
}
