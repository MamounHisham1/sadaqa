<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The recipient name is optional now (a general sadaqa has no name).
        Schema::table('stream_links', function (Blueprint $table) {
            $table->string('recipient_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('stream_links')->whereNull('recipient_name')->update(['recipient_name' => '']);
        Schema::table('stream_links', function (Blueprint $table) {
            $table->string('recipient_name')->nullable(false)->change();
        });
    }
};
