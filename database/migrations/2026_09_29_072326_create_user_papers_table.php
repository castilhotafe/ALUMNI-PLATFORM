<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_papers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('research_paper_id')->constrained('research_papers')->cascadeOnDelete();
            $table->unsignedInteger('author_order')->default(1);
            $table->timestamps();
            $table->unique(['user_id', 'research_paper_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_papers');
    }
};
