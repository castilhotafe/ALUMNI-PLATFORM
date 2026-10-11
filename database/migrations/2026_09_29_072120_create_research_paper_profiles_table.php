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
        Schema::create('research_paper_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_paper_id')->unique()->constrained('research_papers')->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->json('details')->nullable();
            $table->string('picture_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_paper_profiles');
    }
};
