<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFishToUtentiTable extends Migration
{
    public function up()
    {
        Schema::table('utenti', function (Blueprint $table) {
            $table->string('fish')->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('utenti', function (Blueprint $table) {
            $table->dropColumn('fish');
        });
    }
}
