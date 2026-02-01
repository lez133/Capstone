<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssistedByToBeneficiariesTable extends Migration
{
    public function up()
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->unsignedBigInteger('assisted_by')->nullable()->after('verified');
            $table->foreign('assisted_by')->references('id')->on('mswd_members')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropForeign(['assisted_by']);
            $table->dropColumn('assisted_by');
        });
    }
}
