<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() :void
    {
        Schema::create('repairs', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code')->unique(); // Similar to AAA-0001-RS
            $table->string('device_name');
            $table->string('model');
            $table->string('type'); // Type of product
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled']);
            $table->text('description');
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('technician_id')->constrained('technicians');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
