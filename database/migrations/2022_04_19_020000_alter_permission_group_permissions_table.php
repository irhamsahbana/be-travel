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
        Schema::table('permission_group_permissions', function (Blueprint $table) {
            $table->index('company_id');
            $table->foreign('company_id')
            ->references('id')->on('companies')
            ->onDelete('cascade');

            $table->index('permission_group_id');
            $table->foreign('permission_group_id')
            ->references('id')->on('categories')
            ->onDelete('cascade');

            $table->index('permission_id');
            $table->foreign('permission_id')
            ->references('id')->on('categories')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permission_group_permissions', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id']);

            $table->dropForeign(['permission_group_id']);
            $table->dropIndex(['permission_group_id']);

            $table->dropForeign(['permission_id']);
            $table->dropIndex(['permission_id']);
        });
    }
};
