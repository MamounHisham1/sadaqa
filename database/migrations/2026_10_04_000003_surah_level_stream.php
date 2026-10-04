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
        Schema::table('stream_state', function (Blueprint $table) {
            $table->unsignedSmallInteger('surah')->default(1)->after('id');
        });
        DB::table('stream_state')->update(['surah' => DB::raw('LEAST(global_ayah, 114)'), 'duration' => 30]);
        Schema::table('stream_state', function (Blueprint $table) {
            $table->dropColumn('global_ayah');
        });

        Schema::dropIfExists('ayah_durations');
        Schema::create('surah_durations', function (Blueprint $table) {
            $table->id();
            $table->string('reciter_id', 32);
            $table->unsignedSmallInteger('surah');
            $table->unsignedDecimal('seconds', 8, 2);
            $table->unique(['reciter_id', 'surah']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surah_durations');
        Schema::table('stream_state', function (Blueprint $table) {
            $table->unsignedSmallInteger('global_ayah')->default(1)->after('id');
        });
        Schema::table('stream_state', function (Blueprint $table) {
            $table->dropColumn('surah');
        });
    }
};
