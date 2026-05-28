<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            // 💡 categoriesテーブルとのリレーション（親が消えたら連動削除）
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->tinyInteger('gender'); // 1:男性, 2:女性, 3:その他
            $table->string('email', 255);
            $table->string('tel', 11);     // ハイフンなし（10〜11桁）
            $table->string('address', 255);
            $table->string('building', 255)->nullable(); // 建物名は任意（NULL許可）
            $table->string('detail', 120); // お問い合わせ内容（120文字制限）
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
