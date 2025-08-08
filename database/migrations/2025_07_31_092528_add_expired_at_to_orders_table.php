<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dateTime('expired_at')->nullable()->after('payment_status');
    });
}

public function down()
{
    if (Schema::hasTable('orders')) {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('expired_at');
        });
    }
}

};
