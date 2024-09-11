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
        Schema::create('wrok_sheet_solves', function (Blueprint $table) {
            $table->id();
            $table->string('solve');
            $table->foreignId('file_id')->constrained('files')->onDelete('cascade');
            $table->char('type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wrok_sheet_solves');
    }
};
