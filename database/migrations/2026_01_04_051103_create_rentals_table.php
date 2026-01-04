<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('Renter');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');

            // Rental Details
            $table->integer('quantity');
            $table->date('start_date');
            $table->date('end_date');

            // Status Flow
            $table->enum('status', ['pending', 'paid', 'active', 'completed', 'cancelled'])->default('pending');

            // Payment (Midtrans Integration)
            $table->decimal('total_price', 15, 2);
            $table->string('snap_token')->nullable()->comment('Midtrans Snap Token');

            // Return & Fines Mechanism
            $table->dateTime('actual_return_date')->nullable()->comment('Filled via QR Scan');
            $table->decimal('fine_total', 15, 2)->default(0);
            $table->text('fine_notes')->nullable();
            $table->enum('fine_status', ['unpaid', 'paid'])->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
