<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('room_id')
                ->nullable()
                ->constrained('rooms')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop the foreign key and column if they exist
            if (Schema::hasColumn('bookings', 'room_id')) {
                $table->dropConstrainedForeignId('room_id');
            }
        });
    }
};