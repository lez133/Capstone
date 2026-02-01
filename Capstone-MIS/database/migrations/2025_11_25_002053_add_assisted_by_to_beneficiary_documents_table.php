<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficiary_documents', function (Blueprint $table) {
            // nullable FK to mswd_members who assisted the submission
            $table->unsignedBigInteger('assisted_by')->nullable()->after('active');

            $table->foreign('assisted_by', 'benef_doc_assisted_by_fk')
                ->references('id')->on('mswd_members')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('beneficiary_documents', function (Blueprint $table) {
            $table->dropForeign('benef_doc_assisted_by_fk');
            $table->dropColumn('assisted_by');
        });
    }
};
