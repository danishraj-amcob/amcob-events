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
        Schema::create('event_registration_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->nullable();
            $table->string('event_slug');
            $table->string('event_title')->nullable();
            $table->string('ticket_id')->nullable();
            $table->string('ticket_name')->nullable();
            $table->string('email');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->default('pending'); // confirmed | failed
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('check_in_token')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registration_logs');
    }
};
