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
        Schema::create('order_financial_projections', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->primary();
            $table->bigInteger('total_amount');
            $table->bigInteger('paid_amount');
            $table->bigInteger('refunded_amount')->default(0);
            $table->string('refund_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_financial_projections_');
    }
};
