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
        Schema::create('payme_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction')->nullable();
            $table->string('code')->nullable();
            $table->string('state')->nullable();
            $table->string('owner_id')->nullable();
            $table->bigInteger('amount')->nullable();
            $table->string('reason')->nullable();
            $table->string('payme_time')->nullable();
            $table->string('cancel_time')->nullable();
            $table->string('create_time')->nullable();
            $table->string('perform_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payme_transactions');
    }
};