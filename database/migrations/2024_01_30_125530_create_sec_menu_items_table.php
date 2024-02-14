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
        Schema::create('sec_menu_items', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_id');
            $table->string('menu_title');
            $table->string('module');
            $table->unsignedBigInteger('parent_menu')->nullable();
            $table->string('createby');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sec_menu_items');
    }
};
