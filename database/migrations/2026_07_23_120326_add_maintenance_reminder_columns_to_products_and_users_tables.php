<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->unsignedTinyInteger('maintenance_interval_months')->default(12);
            $table->boolean('maintenance_reminders_enabled')->default(true);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('maintenance_reminders_enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['maintenance_interval_months', 'maintenance_reminders_enabled']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('maintenance_reminders_enabled');
        });
    }
};
