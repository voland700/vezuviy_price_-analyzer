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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku');
            $table->string('brand')->nullable();
            $table->string('category')->nullable();
            $table->boolean('available')->default(0);
            $table->boolean('store')->default(0);
            $table->string('url', 1000)->nullable();
            $table->integer('price')->default(0);
            $table->json('pictures')->nullable();
            $table->text('description')->nullable();
            $table->json('params')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
