<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAvatarToBeneficiariesTable extends Migration
{
    public function up()
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            // nullable string to store storage path (e.g. "avatars/xxx.jpg")
            $table->string('avatar')->nullable()->after('password');
        });
    }

    public function down()
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });
    }
}
