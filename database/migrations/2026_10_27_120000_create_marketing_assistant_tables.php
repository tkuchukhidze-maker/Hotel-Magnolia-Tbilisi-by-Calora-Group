<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_marketing_admin')->default(false);
        });

        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 120);
            $table->string('goal', 32);
            $table->string('audience', 160);
            $table->string('market', 80);
            $table->text('offer')->nullable();
            $table->json('channels');
            $table->decimal('budget_gel', 12, 2)->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->string('status', 24)->default('draft');
            $table->json('generated_content')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_campaigns');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_marketing_admin');
        });
    }
};
