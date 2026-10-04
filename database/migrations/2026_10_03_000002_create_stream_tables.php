<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One-row global "radio" position: the whole site is a single station.
        Schema::create('stream_state', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('global_ayah')->default(1); // 1..6236
            $table->unsignedInteger('pass')->default(0);            // completed khatmas
            $table->dateTime('started_at');                          // when this ayah began
            $table->decimal('duration', 8, 2);               // seconds used for it
        });

        // Cached per-reciter audio durations (seconds), filled lazily by HEAD
        // content-length probes. Database, not RAM.
        Schema::create('ayah_durations', function (Blueprint $table) {
            $table->id();
            $table->string('reciter_id', 32);
            $table->unsignedSmallInteger('global_ayah');
            $table->decimal('seconds', 8, 2);
            $table->unique(['reciter_id', 'global_ayah']);
        });

        // Better write concurrency for the hot state row.
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA journal_mode=WAL');
        }

        DB::table('stream_state')->insert([
            'global_ayah' => 1,
            'pass' => 0,
            'started_at' => now(),
            'duration' => 8.0, // provisional; refined by the first probe
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('stream_state');
        Schema::dropIfExists('ayah_durations');
    }
};
