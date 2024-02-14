<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sec_user_access_tbls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fk_role_id');
            $table->unsignedBigInteger('fk_user_id');
            $table->timestamps();

            // Add foreign key constraints if needed
            $table->foreign('fk_role_id')->references('id')->on('roles');
            $table->foreign('fk_user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sec_user_access_tbls');
    }
};
