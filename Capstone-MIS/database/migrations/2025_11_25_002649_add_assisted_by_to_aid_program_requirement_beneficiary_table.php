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
        Schema::table('aid_program_requirement_beneficiary', function (Blueprint $table) {
            $table->unsignedBigInteger('assisted_by')->nullable();

            $table->foreign('assisted_by')
                ->references('id')
                ->on('mswd_members')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('aid_program_requirement_beneficiary', function (Blueprint $table) {
            $table->dropForeign(['assisted_by']);
            $table->dropColumn('assisted_by');
        });
    }
};
