<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Dùng SQL thô để xóa khóa ngoại nếu tồn tại
        // 1. Lấy danh sách các foreign key hiện có
        $foreignKeys = DB::select("SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = 'product_variants'
              AND TABLE_SCHEMA = DATABASE()
              AND REFERENCED_TABLE_NAME IS NOT NULL");

        $fkNames = collect($foreignKeys)->pluck('CONSTRAINT_NAME')->toArray();

        if (in_array('product_variants_color_id_foreign', $fkNames)) {
            DB::statement('ALTER TABLE product_variants DROP FOREIGN KEY product_variants_color_id_foreign');
        }

        if (in_array('product_variants_size_id_foreign', $fkNames)) {
            DB::statement('ALTER TABLE product_variants DROP FOREIGN KEY product_variants_size_id_foreign');
        }

        // Sau đó mới xoá cột nếu có
        Schema::table('product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_variants', 'color_id')) {
                $table->dropColumn('color_id');
            }

            if (Schema::hasColumn('product_variants', 'size_id')) {
                $table->dropColumn('size_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants', 'color_id')) {
                $table->unsignedBigInteger('color_id')->nullable();
            }

            if (!Schema::hasColumn('product_variants', 'size_id')) {
                $table->unsignedBigInteger('size_id')->nullable();
            }

            $table->foreign('color_id')->references('id')->on('colors')->onDelete('set null');
            $table->foreign('size_id')->references('id')->on('sizes')->onDelete('set null');
        });
    }
};
