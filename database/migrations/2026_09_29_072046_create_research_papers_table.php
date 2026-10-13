<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_papers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('visibility')->default('public')->after('abstract');
            $table->text('abstract');
            $table->string('doi')->nullable()->unique();
            $table->string('pdf_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_papers');
    }
};
