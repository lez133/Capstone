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

            // verification fields
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable(); // mswd_members.id
            $table->timestamp('expires_at')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->foreign('beneficiary_id')->references('id')->on('beneficiaries')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('mswd_members')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('beneficiary_documents', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropForeign(['beneficiary_id']);
        });

        Schema::dropIfExists('beneficiary_documents');
    }
}
