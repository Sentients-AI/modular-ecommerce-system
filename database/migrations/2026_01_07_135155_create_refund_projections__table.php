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
        Schema::create('refund_projections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('refund_id')->unique();
            $table->unsignedBigInteger('order_id');
            $table->bigInteger('amount_cents');
            $table->string('status');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('succeeded_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_projections_');
    }
};
