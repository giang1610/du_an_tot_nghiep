<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Nếu bạn dùng foreign key, nên drop foreign key trước
            if (Schema::hasColumn('products', 'color_id')) {
                $table->dropForeign(['color_id']);
                $table->dropColumn('color_id');
            }

            if (Schema::hasColumn('products', 'size_id')) {
                $table->dropForeign(['size_id']);
                $table->dropColumn('size_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Nếu muốn rollback, thêm lại 2 cột
            $table->unsignedBigInteger('color_id')->nullable();
            $table->unsignedBigInteger('size_id')->nullable();

            // Nếu có quan hệ foreign key, thêm lại
            $table->foreign('color_id')->references('id')->on('colors')->onDelete('set null');
            $table->foreign('size_id')->references('id')->on('sizes')->onDelete('set null');
        });
    }
};
