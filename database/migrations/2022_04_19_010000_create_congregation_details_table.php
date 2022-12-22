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
        Schema::create('congregation_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('person_id');
            $table->boolean('is_has_meningitis_vaccinated')->default(false);
            $table->boolean('is_has_family_card')->default(false);
            $table->boolean('is_has_photo')->default(false);
            $table->boolean('is_has_mahram')->default(false);
            $table->boolean('is_airport_handling')->default(false);
            $table->boolean('is_equipment')->default(false);
            $table->boolean('is_single_mahram')->default(false);
            $table->boolean('is_double_mahram')->default(false);
            $table->boolean('is_pusher_guide')->default(false);
            $table->boolean('is_special_guide')->default(false);
            $table->boolean('is_manasik')->default(false);
            $table->boolean('is_domestic_ticket')->default(false);
            $table->enum('blood_type', ['A', 'B', 'AB', 'O'])->nullable();
            $table->string('chronic_disease')->nullable();
            $table->string('allergy')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('passport_issued_in')->nullable();
            $table->date('passport_issued_at')->nullable();
            $table->date('passport_expired_at')->nullable();
            $table->string('passport_name')->nullable();
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
        Schema::dropIfExists('congregation_details');
    }
};
