<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCentenarianCashGiftsTable extends Migration
{
    public function up()
    {
        Schema::create('centenarian_cash_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained()->onDelete('cascade');
            $table->integer('milestone_age');
            $table->date('given_at')->nullable();
            $table->timestamps();
            $table->unique(['beneficiary_id', 'milestone_age']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('centenarian_cash_gifts');
    }
}
