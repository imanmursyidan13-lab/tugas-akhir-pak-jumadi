<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Balasan terhadap komentar (kritik) milik user lain. Satu level saja (tidak bersarang).
        Schema::create('balasans', function (Blueprint $table) {
            $table->id();
            $table->text('comment');
            $table->foreignId('kritik_id')->constrained('kritiks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balasans');
    }
};
