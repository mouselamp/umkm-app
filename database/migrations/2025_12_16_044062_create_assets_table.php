<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->string('name');
            $table->string('asset_number')->unique();
            $table->date('purchase_date');
            $table->decimal('purchase_price', 15, 2);
            $table->integer('useful_life_month');
            $table->decimal('residual_value', 15, 2)->default(0);
            $table->decimal('book_value', 15, 2);
            $table->enum('payment_type', ['cash', 'credit']);
            $table->enum('status', ['active', 'disposed', 'fully_depreciated'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
