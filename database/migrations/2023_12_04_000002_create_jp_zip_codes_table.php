<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * マイグレーションを実行する。
     */
    public function up(): void
    {
        Schema::create('jp_zip_codes', function (Blueprint $table) {
            $table->id();
            $table->string('zip_code', 7)->unique();
            $table->string('prefecture', 4);
            $table->string('city', 10)->nullable();
            $table->string('town', 50)->nullable();
            $table->string('office_address', 50)->nullable();
            $table->string('office_name', 100)->nullable();
        });
    }

    /**
     * マイグレーションを元に戻す。
     */
    public function down(): void
    {
        Schema::dropIfExists('jp_zip_codes');
    }
};
