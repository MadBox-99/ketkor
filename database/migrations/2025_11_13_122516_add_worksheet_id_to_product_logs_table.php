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
        Schema::table('product_logs', function (Blueprint $table) {
            $table->text('worksheet_id')->nullable()->after('is_online');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_logs', function (Blueprint $table) {
            $table->dropColumn('worksheet_id');
        });
    }
};
