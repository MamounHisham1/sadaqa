<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A link may set a password so its owner can edit it later.
        Schema::table('stream_links', function (Blueprint $table) {
            $table->string('password')->nullable()->after('rotation');
        });

        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16)->default('bug'); // bug | feature
            $table->text('message');
            $table->string('contact')->nullable();
            $table->string('url')->nullable();
            $table->boolean('resolved')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
        if (Schema::hasColumn('stream_links', 'password')) {
            Schema::table('stream_links', function (Blueprint $table) {
                $table->dropColumn('password');
            });
        }
    }
};
