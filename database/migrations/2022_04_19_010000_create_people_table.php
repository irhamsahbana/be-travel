<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('people', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('branch_id')->nullable();
            $table->uuid('category_id');
            $table->uuid('agent_id')->nullable();
            $table->uuid('congregation_id')->nullable();
            $table->uuid('province_id')->nullable();
            $table->uuid('city_id')->nullable();
            $table->uuid('marital_status_id')->nullable();
            $table->uuid('nationality_id')->nullable();
            $table->uuid('education_id')->nullable();
            $table->uuid('bank_id')->nullable();
            $table->string('ref_no')->unique()->nullable();
            $table->string('name');
            $table->enum('sex', ['male', 'female'])->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('wa')->nullable();
            $table->string('national_id')->nullable();
            $table->string('address')->nullable();
            $table->string('profession')->nullable();
            $table->string('emergency_name')->nullable();
            $table->string('emergency_address')->nullable();
            $table->string('emergency_home_phone')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->string('notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('people');
    }
}
