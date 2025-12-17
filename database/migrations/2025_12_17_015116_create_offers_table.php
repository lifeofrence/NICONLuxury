<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('discount'); // e.g., "10% OFF", "Kids Stay Free"
            $table->text('description');
            $table->string('valid_until'); // e.g., "March 31, 2026", "Ongoing"
            $table->json('terms'); // Array of terms/conditions
            $table->string('image_path');
            $table->string('badge')->nullable(); // e.g., "New", "Popular"
            $table->enum('badge_variant', ['default', 'destructive', 'secondary'])->default('default');
            $table->enum('offer_type', ['main', 'seasonal'])->default('main'); // Main offer or seasonal
            $table->string('icon')->nullable(); // For seasonal offers (e.g., "Gift", "Star", "Percent")
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
