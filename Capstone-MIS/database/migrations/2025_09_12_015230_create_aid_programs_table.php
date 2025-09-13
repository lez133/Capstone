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
        Schema::create('aid_programs', function (Blueprint $table) {
            $table->id();
            $table->string('aid_program_name');
            $table->text('description');
            $table->foreignId('program_type_id')->constrained('program_types')->onDelete('cascade'); // Foreign key to program_types
            $table->string('background_image')->nullable();
            $table->string('default_background')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aid_programs');
    }
};
