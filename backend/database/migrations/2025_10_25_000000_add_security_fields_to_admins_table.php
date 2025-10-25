<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->unsignedInteger('failed_login_count')->default(0);
            $table->unsignedInteger('twofa_failed_count')->default(0);
            $table->unsignedInteger('total_failed_count')->default(0);
            $table->timestamp('temporary_lock_until')->nullable();
            $table->boolean('is_suspended')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn([
                'failed_login_count',
                'twofa_failed_count',
                'total_failed_count',
                'temporary_lock_until',
                'is_suspended',
            ]);
        });
    }
};

