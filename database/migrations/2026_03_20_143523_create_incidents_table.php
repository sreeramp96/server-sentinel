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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at')->index();
            $table->timestamp('resolved_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->timestamps();
        });

        Schema::table('websites', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('is_active');
            $table->string('public_slug')->nullable()->unique()->after('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn(['is_public', 'public_slug']);
        });
    }
};
