<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stream_links', function (Blueprint $table) {
            $table->id();
            $table->string('token', 10)->unique();
            $table->string('recipient_name');
            $table->string('dedication_type')->default('memory'); // memory | healing | gift
            $table->string('sender_name')->nullable();
            $table->text('message')->nullable();
            $table->string('reciter')->nullable(); // null = rotate through all reciters
            $table->unsignedTinyInteger('start_surah')->default(1);
            $table->unsignedBigInteger('ayahs_played')->default(0);
            $table->unsignedBigInteger('khatmas')->default(0);
            $table->unsignedBigInteger('views')->default(0);
            $table->timestamp('last_played_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stream_links');
    }
};
