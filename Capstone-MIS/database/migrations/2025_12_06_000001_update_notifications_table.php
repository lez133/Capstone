<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Add new columns
            $table->string('sender_name')->nullable()->after('sender_id');
            $table->string('recipient')->nullable()->after('sender_name');
            $table->string('subject')->nullable()->after('recipient');
            $table->string('status')->default('sent')->after('message');

            // Drop old foreign key and update sender_id to reference mswd_members
            $table->dropForeign(['sender_id']);
            $table->foreign('sender_id')->references('id')->on('mswd_members')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['sender_name', 'recipient', 'subject', 'status']);
            $table->dropForeign(['sender_id']);
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};
