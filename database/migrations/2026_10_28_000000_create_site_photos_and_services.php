<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Photos and services the hotel manages from the admin panel.
 * The current gallery and amenities are copied in, so nothing changes on the
 * site until someone edits them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_photos', function (Blueprint $table) {
            $table->id();
            $table->string('slot', 64)->nullable()->unique(); // e.g. room.double, home.hero; null = gallery photo
            $table->string('path');                           // assets/img/... (built in) or a path on the media disk
            $table->string('alt', 255)->nullable();
            $table->string('caption', 160)->nullable();
            $table->string('category', 64)->nullable();       // gallery: "rooms", "details spaces", …
            $table->boolean('wide')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('hotel_services', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 16);                       // amenity | extra
            $table->string('name', 90);
            $table->string('description', 255)->nullable();
            $table->string('icon', 24)->default('leaf');
            $table->unsignedInteger('price')->nullable();     // GEL, extras only
            $table->string('price_unit', 16)->nullable();     // stay | night | guest | guest_night | room_night
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->json('extras')->nullable()->after('items');
            $table->unsignedInteger('extras_total')->default(0)->after('nightly_price');
        });

        $now = now();
        $gallery = [
            ['rooftop-view', 'spaces', true, 'Rooftop terrace with glass domes facing Mount Mtatsminda and the TV tower', 'Glass domes on the roof, Mtatsminda straight ahead.'],
            ['bathroom', 'details rooms', false, 'Bathroom with black-and-white patterned tiles', 'Patterned tiles, plenty of hot water.'],
            ['room-deluxe', 'rooms', false, 'Deluxe Room under two skylights', 'Wake up to sky, not street.'],
            ['room-double', 'rooms', false, 'Double Room with a skylight over a writing desk', 'A desk under the skylight.'],
            ['room-twin', 'details rooms', false, 'A gold-framed painting of haystacks above twin beds', 'Real paintings, in real gold frames.'],
            ['welcome-tray', 'details', false, 'Welcome tea and fruit on a small black table', 'Tea and fruit, waiting for you.'],
            ['lobby', 'spaces', false, 'The lobby with chandeliers and red armchairs', 'The lobby, for slow mornings.'],
            ['aerial', 'spaces', true, 'The hotel from above: old brick walls under a white louvred roof', 'Old brick below, a light new roof above.'],
            ['facade-street', 'spaces', true, 'The brick facade with tall wooden windows on a quiet street', 'A quiet street in the old town.'],
        ];
        foreach ($gallery as $i => [$img, $cat, $wide, $alt, $caption]) {
            DB::table('site_photos')->insert([
                'path' => 'assets/img/'.$img.'.webp', 'alt' => $alt, 'caption' => $caption, 'category' => $cat,
                'wide' => $wide, 'is_visible' => true, 'sort' => $i + 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $amenities = [
            ['Buffet breakfast', 'Every morning, served in the lobby.', 'coffee'],
            ['Free parking', 'Rare in the old town. Free for every guest.', 'parking'],
            ['Free Wi‑Fi', 'In every room and on the terrace.', 'wifi'],
            ['24-hour front desk', "Someone's always here.", 'bell'],
            ['Garden & terrace', 'Green below, the whole city above.', 'leaf'],
            ['Family rooms', 'Triples with a balcony.', 'family'],
            ['Air conditioning', 'In every room.', 'snow'],
        ];
        foreach ($amenities as $i => [$name, $text, $icon]) {
            DB::table('hotel_services')->insert([
                'kind' => 'amenity', 'name' => $name, 'description' => $text, 'icon' => $icon,
                'is_active' => true, 'sort' => $i + 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['extras', 'extras_total']);
        });
        Schema::dropIfExists('hotel_services');
        Schema::dropIfExists('site_photos');
    }
};
