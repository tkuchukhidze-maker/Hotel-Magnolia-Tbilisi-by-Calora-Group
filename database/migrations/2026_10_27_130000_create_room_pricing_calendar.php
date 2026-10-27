<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('room_rate_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();
            $table->string('name', 90);
            $table->boolean('includes_breakfast')->default(false);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
        Schema::create('room_plan_prices', function (Blueprint $table) {
            $table->id();
            $table->string('room_type', 32);
            $table->string('plan_code', 8);
            $table->unsignedInteger('one_guest');
            $table->unsignedInteger('two_guests');
            $table->unique(['room_type', 'plan_code']);
        });
        Schema::create('room_month_prices', function (Blueprint $table) {
            $table->id();
            $table->string('room_type', 32);
            $table->string('plan_code', 8);
            $table->char('month', 7);
            $table->unsignedInteger('one_guest');
            $table->unsignedInteger('two_guests');
            $table->unique(['room_type', 'plan_code', 'month']);
        });
        Schema::create('room_day_prices', function (Blueprint $table) {
            $table->id();
            $table->string('room_type', 32);
            $table->string('plan_code', 8);
            $table->date('stay_date');
            $table->unsignedInteger('one_guest');
            $table->unsignedInteger('two_guests');
            $table->unique(['room_type', 'plan_code', 'stay_date']);
            $table->index('stay_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_day_prices');
        Schema::dropIfExists('room_month_prices');
        Schema::dropIfExists('room_plan_prices');
        Schema::dropIfExists('room_rate_plans');
    }
};
