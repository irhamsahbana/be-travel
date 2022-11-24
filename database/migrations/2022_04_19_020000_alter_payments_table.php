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
        Schema::table('payments', function (Blueprint $table) {
            $table->index('invoice_id');
            $table->foreign('invoice_id')
            ->references('id')->on('invoices');

            $table->index('payment_method_id');
            $table->foreign('payment_method_id')
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
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropIndex(['invoice_id']);

            $table->dropForeign(['payment_method_id']);
            $table->dropIndex(['payment_method_id']);
        });
    }
};
