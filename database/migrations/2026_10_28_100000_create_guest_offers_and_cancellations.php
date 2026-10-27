<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Offer emails to past guests, and cancelling a booking (guest request / no-show)
 * with an email to the guest.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason', 20)->nullable(); // guest | no_show | hotel
            $table->text('cancel_note')->nullable();
            $table->timestamp('cancel_emailed_at')->nullable();
        });

        Schema::create('offer_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 150);
            $table->string('headline', 150);
            $table->text('body');
            $table->unsignedTinyInteger('discount')->nullable();
            $table->string('code', 40)->nullable();
            $table->string('button_label', 60);
            $table->string('button_url', 500);
            $table->date('valid_until')->nullable();
            $table->string('audience', 20);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('offer_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->timestamp('sent_at')->nullable();
            $table->string('error', 255)->nullable();
            $table->timestamps();
            $table->unique(['offer_campaign_id', 'email']);
        });

        Schema::create('email_unsubscribes', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_unsubscribes');
        Schema::dropIfExists('offer_recipients');
        Schema::dropIfExists('offer_campaigns');
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['cancelled_at', 'cancel_reason', 'cancel_note', 'cancel_emailed_at']);
        });
    }
};
