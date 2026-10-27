<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One booking can now hold several rooms of several types, and the ages of
 * the children. `room` keeps the first room type for quick filtering.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->json('items')->nullable()->after('reference');
            $table->unsignedTinyInteger('rooms_count')->default(1)->after('items');
            $table->json('children_ages')->nullable()->after('children');
            $table->string('room', 32)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['items', 'rooms_count', 'children_ages']);
        });
    }
};
