<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogsTable extends Migration
{
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('action')->index(); // e.g. created, updated, deleted, login, export
            $table->string('subject_type')->nullable()->index(); // model class or area
            $table->unsignedBigInteger('subject_id')->nullable()->index();
            $table->json('meta')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['action','created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
}
