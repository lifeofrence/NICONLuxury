<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number', 10)->unique();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnUpdate()->restrictOnDelete();
            $table->enum('status', ['Available', 'Occupied', 'Under Maintenance', 'Dirty'])->default('Available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
