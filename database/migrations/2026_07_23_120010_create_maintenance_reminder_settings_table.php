<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('maintenance_reminder_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->json('advance_days');
            $table->unsignedSmallInteger('overdue_repeat_days')->default(14);
            $table->unsignedTinyInteger('overdue_max_count')->default(3);
            $table->string('contact_phone', 100)->nullable();
            $table->string('contact_email', 255)->nullable();
            $table->string('booking_url', 500)->nullable();
            $table->string('email_subject', 255);
            $table->text('email_body');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_reminder_settings');
    }
};
