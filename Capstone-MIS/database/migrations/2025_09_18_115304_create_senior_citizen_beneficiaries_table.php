<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeniorCitizenBeneficiariesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('senior_citizen_beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('barangay_id'); // Foreign key to barangays table
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->date('birthday');
            $table->integer('age');
            $table->string('gender');
            $table->string('civil_status');
            $table->string('osca_number');
            $table->date('date_issued')->nullable();
            $table->string('remarks')->nullable();
            $table->string('national_id')->nullable();
            $table->string('pkn')->nullable();
            $table->string('rrn')->nullable();
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('barangay_id')->references('id')->on('barangays')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('senior_citizen_beneficiaries');
    }
};
