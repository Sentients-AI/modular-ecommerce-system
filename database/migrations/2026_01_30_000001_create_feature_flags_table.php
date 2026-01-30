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
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(false)->index();
            $table->json('conditions')->nullable();
            $table->unsignedBigInteger('enabled_by')->nullable();
            $table->timestamp('enabled_at')->nullable();
            $table->unsignedBigInteger('disabled_by')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->timestamps();

            $table->foreign('enabled_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('disabled_by')->references('id')->on('users')->nullOnDelete();
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
    }
};
