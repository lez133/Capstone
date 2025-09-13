<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMswdMembersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('mswd_members', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('fname'); // First name
            $table->string('mname')->nullable(); // Middle name (optional)
            $table->string('lname'); // Last name
            $table->date('birthday'); // Birthday
            $table->string('gender'); // Gender
            $table->string('role'); // Role (e.g., MSWD Representative, Barangay Representative)
            $table->string('email')->unique(); // Email address
            $table->string('contact'); // Contact number
            $table->string('profile_picture')->nullable(); // Profile picture (optional)
            $table->string('username')->unique(); // Username (must be unique)
            $table->string('password'); // Password (hashed)
            $table->unsignedBigInteger('created_by')->nullable(); // ID of the user who created the member
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null'); // Foreign key to users table
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('mswd_members');
    }
}
