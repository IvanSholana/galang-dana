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
        Schema::create('fundraising_phases', function (Blueprint $table) {
            $table->id();
            // $table->bigInteger('fundraising_id'); // Change column type to bigInteger
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
            $table->string('notes')->nullable(); // Add new column

            // Add foreign key constraint
            $table->foreignId('fundraising_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fundraising_phases');
    }
};
