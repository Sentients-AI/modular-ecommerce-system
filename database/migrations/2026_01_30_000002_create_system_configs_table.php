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
        Schema::create('system_configs', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->index();
            $table->string('key', 100);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type', 20)->default('string');
            $table->text('value')->nullable();
            $table->text('default_value')->nullable();
            $table->json('validation_rules')->nullable();
            $table->boolean('is_sensitive')->default(false);
            $table->timestamps();

            $table->unique(['group', 'key']);
            $table->index(['group', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_configs');
    }
};
