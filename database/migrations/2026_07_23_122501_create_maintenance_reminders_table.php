<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_reminders', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Product::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('email', 255);
            $table->string('stage', 20);
            $table->unsignedSmallInteger('stage_key');
            $table->date('due_date');
            $table->date('last_maintenance_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('status', 20);
            $table->text('error')->nullable();
            $table->timestamps();

            $table->unique(
                ['product_id', 'user_id', 'due_date', 'stage', 'stage_key'],
                'maintenance_reminders_unique_occurrence',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_reminders');
    }
};
