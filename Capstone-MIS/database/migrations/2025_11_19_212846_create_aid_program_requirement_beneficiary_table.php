<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aid_program_requirement_beneficiary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained('beneficiaries')->onDelete('cascade');
            $table->foreignId('barangay_id')->constrained('barangays')->onDelete('cascade');
            $table->foreignId('aid_program_id')->constrained('aid_programs')->onDelete('cascade');
            $table->foreignId('requirement_id')->constrained('requirements')->onDelete('cascade');
            $table->unsignedBigInteger('beneficiary_document_id')->nullable();
            $table->foreign('beneficiary_document_id', 'apr_beneficiary_document_fk')
                ->references('id')->on('beneficiary_documents')->onDelete('set null');
            $table->enum('status', ['Pending', 'Validated', 'Rejected'])->default('Pending');
            $table->text('remarks')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->unique(['beneficiary_id', 'aid_program_id', 'requirement_id'], 'unique_beneficiary_requirement');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aid_program_requirement_beneficiary');
    }
};
