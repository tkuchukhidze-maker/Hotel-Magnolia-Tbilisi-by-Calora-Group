<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('room_rates', function (Blueprint $table) {
            $table->id();
            $table->string('room_type')->unique();
            $table->unsignedInteger('one_room');
            $table->unsignedInteger('one_breakfast');
            $table->unsignedInteger('two_room');
            $table->unsignedInteger('two_breakfast');
            $table->timestamp('updated_at')->nullable();
        });
        Schema::create('hotel_promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 32)->unique();
            $table->unsignedTinyInteger('discount_percent');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('hotel_promotions');
        Schema::dropIfExists('room_rates');
    }
};
