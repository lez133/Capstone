<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('aid_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('beneficiary_id')->index();
            $table->unsignedBigInteger('aid_program_id')->index();
            $table->unsignedBigInteger('schedule_id')->nullable()->index();
            $table->timestamp('receipt_date')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable()->comment('admin id when confirmed by admin');
            $table->timestamps();

            // add FKs if you have these tables
            $table->foreign('beneficiary_id')->references('id')->on('beneficiaries')->onDelete('cascade');
            $table->foreign('aid_program_id')->references('id')->on('aid_programs')->onDelete('cascade');
            // schedule_id FK optional
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aid_receipts');
    }
};
