<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePWDBeneficiariesTable extends Migration
{
    public function up()
    {
        Schema::create('pwd_beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->unsignedBigInteger('barangay_id');
            $table->enum('gender', ['M', 'F']);
            $table->text('type_of_disability');
            $table->text('pwd_id_number');
            $table->text('remarks')->nullable();
            $table->date('birthday')->nullable();
            $table->integer('age')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->integer('validity_years')->default(5);
            $table->timestamps();

            $table->foreign('barangay_id')->references('id')->on('barangays')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pwd_beneficiaries');
    }
}
