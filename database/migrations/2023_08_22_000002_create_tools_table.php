<?php

use App\Enums\ProductCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 200)->nullable(false)->default('');
            $table->string('category', 200)->nullable()->default(ProductCategory::KAZAN->value);
            $table->string('tag', 200)->nullable()->default('');
            $table->string('factory_name', 200)->nullable()->default('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
