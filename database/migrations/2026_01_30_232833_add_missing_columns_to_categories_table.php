<?php

declare(strict_types=1);

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
        Schema::table('categories', function (Blueprint $table) {
            $table->text('description')->nullable()->after('slug');
            $table->foreignId('parent_id')->nullable()->after('description')->constrained('categories')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('parent_id');
            $table->string('image')->nullable()->after('is_active');
            $table->integer('sort_order')->default(0)->after('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['description', 'parent_id', 'is_active', 'image', 'sort_order']);
        });
    }
};
