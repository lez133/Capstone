<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBarangayIdToMswdMembersTable extends Migration
{
    public function up()
    {
        Schema::table('mswd_members', function (Blueprint $table) {
            $table->unsignedBigInteger('barangay_id')->nullable()->after('role');
            $table->foreign('barangay_id')->references('id')->on('barangays')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('mswd_members', function (Blueprint $table) {
            $table->dropForeign(['barangay_id']);
            $table->dropColumn('barangay_id');
        });
    }
}
