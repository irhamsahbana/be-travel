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
        Schema::create('logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->nullable();
            $table->uuid('branch_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->uuid('resource_id')->nullable();
            $table->string('level')->nullable();
            $table->string('table')->nullable();
            $table->string('action')->nullable();
            $table->text('data')->nullable();
            $table->string('exception')->nullable();
            $table->string('message')->nullable();
            $table->string('file')->nullable();
            $table->string('line')->nullable();
            $table->text('trace')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
};
