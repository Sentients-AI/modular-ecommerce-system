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
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->bigInteger('old_price_cents')->unsigned()->nullable();
            $table->bigInteger('new_price_cents')->unsigned();
            $table->bigInteger('old_sale_price')->unsigned()->nullable();
            $table->bigInteger('new_sale_price')->unsigned()->nullable();
            $table->timestamp('effective_at');
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('changed_by')->references('id')->on('users')->nullOnDelete();
            $table->index('product_id');
            $table->index('effective_at');
            $table->index(['product_id', 'effective_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};
