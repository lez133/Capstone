<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeneficiaryDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('beneficiary_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('beneficiary_id');
            $table->string('document_type');
            $table->string('file_path');
            $table->string('status')->default('Pending Review');
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();

            $table->foreign('beneficiary_id')->references('id')->on('beneficiaries')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('beneficiary_documents');
    }
}
