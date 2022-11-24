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
        Schema::create('congregation_checklists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('person_id');
            $table->boolean('is_meningitis_vaccinated')->default(false);
            $table->boolean('is_family_card')->default(false);
            $table->boolean('is_photo')->default(false);
            $table->boolean('is_mahram')->default(false);
            $table->boolean('is_airport_handling')->default(false);
            $table->boolean('is_equipment')->default(false);
            $table->boolean('is_single_mahram')->default(false);
            $table->boolean('is_double_mahram')->default(false);
            $table->boolean('is_pusher_guide')->default(false);
            $table->boolean('is_special_guide')->default(false);
            $table->boolean('is_manasik')->default(false);
            $table->boolean('is_domestic_ticket')->default(false);
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
        Schema::dropIfExists('congregation_checklists');
    }
};
