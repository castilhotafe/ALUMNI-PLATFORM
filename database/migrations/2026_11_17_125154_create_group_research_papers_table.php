<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_research_papers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('research_paper_id')->constrained('research_papers')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['group_id', 'research_paper_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_research_papers');
    }
};
