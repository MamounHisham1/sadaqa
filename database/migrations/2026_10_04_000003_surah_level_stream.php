<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The radio now streams full surahs instead of single ayahs.
        // Every step is guarded so the migration tolerates partially
        // applied states (e.g. a DB restored mid-deploy).
        if (! Schema::hasColumn('stream_state', 'surah')) {
            Schema::table('stream_state', function (Blueprint $table) {
                $table->unsignedSmallInteger('surah')->default(1)->after('id');
            });
        }
        if (Schema::hasColumn('stream_state', 'global_ayah')) {
            // MIN() (not LEAST) keeps compatibility with older SQLite versions.
            DB::table('stream_state')->update(['surah' => DB::raw('MIN(global_ayah, 114)'), 'duration' => 30]);
        }

        if (! Schema::hasTable('surah_durations')) {
            Schema::create('surah_durations', function (Blueprint $table) {
                $table->id();
                $table->string('reciter_id', 32);
                $table->unsignedSmallInteger('surah');
                $table->decimal('seconds', 8, 2);
                $table->unique(['reciter_id', 'surah']);
            });
        }
        // global_ayah is dropped in the following migration (000004), which
        // rebuilds stream_state without needing ALTER TABLE DROP COLUMN.
    }

    public function down(): void
    {
        Schema::dropIfExists('surah_durations');
        if (Schema::hasColumn('stream_state', 'surah')) {
            Schema::table('stream_state', function (Blueprint $table) {
                $table->dropColumn('surah');
            });
        }
    }
};
