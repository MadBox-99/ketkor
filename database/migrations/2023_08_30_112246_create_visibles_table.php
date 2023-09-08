<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('are_visible', function (Blueprint $table) {
            $table->id();
            $table->boolean('isVisible')->default(false);
            $table->foreignIdFor(Product::class)->nullable(false);
            $table->foreignIdFor(User::class)->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('are_visible');
    }
};
