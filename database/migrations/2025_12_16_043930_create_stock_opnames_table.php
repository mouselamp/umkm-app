<?php

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
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->enum('type', ['material', 'product']);
            $table->unsignedBigInteger('reference_id');
            $table->decimal('system_qty', 10, 2);
            $table->decimal('actual_qty', 10, 2);
            $table->decimal('difference', 10, 2);
            $table->text('reason')->nullable();
            $table->date('opname_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
