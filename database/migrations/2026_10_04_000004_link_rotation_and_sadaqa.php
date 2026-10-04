<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Each link can pick its reciters; the timeline for a rotation set is
        // a pure function of the epoch, so links share the same broadcast
        // phase while hearing their chosen reciters.
        Schema::table('stream_links', function (Blueprint $table) {
            $table->json('rotation')->nullable()->after('message');
        });
        DB::table('stream_links')
            ->whereIn('dedication_type', ['memory', 'healing'])
            ->update(['dedication_type' => 'sadaqa']);

        // Dedication is now sadaqa or gift only.
        DB::table('stream_links')
            ->whereNotIn('dedication_type', ['sadaqa', 'gift'])
            ->update(['dedication_type' => 'sadaqa']);

        // stream_state becomes a pure broadcast anchor: the epoch. Position is
        // computed, never stored.
        foreach (['surah', 'pass', 'duration'] as $col) {
            DB::statement("ALTER TABLE stream_state DROP COLUMN {$col}");
        }
    }

    public function down(): void
    {
        Schema::table('stream_links', function (Blueprint $table) {
            $table->dropColumn('rotation');
        });
        Schema::table('stream_state', function (Blueprint $table) {
            $table->unsignedSmallInteger('surah')->default(1);
            $table->unsignedInteger('pass')->default(0);
            $table->unsignedDecimal('duration', 8, 2)->default(30);
        });
    }
};
