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
        Schema::create('research_paper_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_paper_id')->constrained('research_papers')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('author_order')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_paper_authors');
    }
};
