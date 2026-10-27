<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 16)->unique();
            $table->string('room', 32);
            $table->string('rate', 8);
            $table->date('checkin');
            $table->date('checkout');
            $table->unsignedSmallInteger('nights');
            $table->unsignedTinyInteger('adults');
            $table->unsignedTinyInteger('children')->default(0);
            $table->unsignedInteger('nightly_price');
            $table->unsignedInteger('total');
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->string('email', 160);
            $table->string('phone', 40);
            $table->string('country', 80);
            $table->string('arrival', 40)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('privacy_accepted_at');
            $table->string('status', 20)->default('requested');
            $table->timestamps();
            $table->index(['checkin', 'room']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
