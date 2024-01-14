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
        Schema::table('customers', function (Blueprint $table) {
            //$table->id();
            //$table->timestamps();
            $table->foreign('id_reg')->references('id_reg')->on('regions')->onDelete('cascade');
            $table->foreign('id_com')->references('id_com')->on('communes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //Schema::dropIfExists('customers');
    }
};
