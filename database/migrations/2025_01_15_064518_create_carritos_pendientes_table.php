<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('carritos_pendientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_users');
            $table->json('productos'); // Aquí guardamos los productos como JSON
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('id_users')->references('id')->on('users')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carritos_pendientes');
    }
};
