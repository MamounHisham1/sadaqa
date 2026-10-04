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
        // phase while hearing their chosen reciters. All steps guarded.
        if (! Schema::hasColumn('stream_links', 'rotation')) {
            Schema::table('stream_links', function (Blueprint $table) {
                $table->json('rotation')->nullable()->after('message');
            });
        }

        DB::table('stream_links')
            ->whereIn('dedication_type', ['memory', 'healing'])
            ->update(['dedication_type' => 'sadaqa']);
        DB::table('stream_links')
            ->whereNotIn('dedication_type', ['sadaqa', 'gift'])
            ->update(['dedication_type' => 'sadaqa']);

        // stream_state becomes a pure broadcast anchor: the epoch. Position is
        // computed, never stored. Rebuild the table instead of ALTER DROP
        // COLUMN so old SQLite versions are fine; the existing started_at is
        // preserved as the epoch.
        $legacyCols = ['global_ayah', 'surah', 'pass', 'duration'];
        $needsRebuild = collect($legacyCols)
            ->contains(fn ($col) => Schema::hasColumn('stream_state', $col));

        if ($needsRebuild) {
            $epochRow = DB::table('stream_state')->where('id', 1)->first();
            $epoch = $epochRow?->started_at ?: now()->format('Y-m-d H:i:s');

            Schema::dropIfExists('stream_state');
            Schema::create('stream_state', function (Blueprint $table) {
                $table->id();
                $table->dateTime('started_at');
            });
            DB::table('stream_state')->insert(['id' => 1, 'started_at' => $epoch]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('stream_links', 'rotation')) {
            Schema::table('stream_links', function (Blueprint $table) {
                $table->dropColumn('rotation');
            });
        }
        if (! Schema::hasColumn('stream_state', 'surah')) {
            Schema::table('stream_state', function (Blueprint $table) {
                $table->unsignedSmallInteger('surah')->default(1);
            });
        }
        if (! Schema::hasColumn('stream_state', 'pass')) {
            Schema::table('stream_state', function (Blueprint $table) {
                $table->unsignedInteger('pass')->default(0);
            });
        }
        if (! Schema::hasColumn('stream_state', 'duration')) {
            Schema::table('stream_state', function (Blueprint $table) {
                $table->decimal('duration', 8, 2)->default(30);
            });
        }
    }
};
