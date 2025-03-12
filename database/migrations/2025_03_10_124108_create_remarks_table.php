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
        Schema::create('remarks', function (Blueprint $table) {
            $table->id();
            $table->enum('gds', ['SABRE', 'AMADEUS']);
            $table->string('record_locator', 6);
            $table->unsignedInteger('reference');
            $table->string('text', 255);
            $table->json('passenger_refs')->nullable();
            $table->json('segment_refs')->nullable();
            $table->timestamps();
        });
    }
};
