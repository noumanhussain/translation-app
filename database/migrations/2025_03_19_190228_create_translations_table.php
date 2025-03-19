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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->text('value');
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->string('group')->default('general');
            $table->timestamps();

            // Create indexes for common query patterns
            $table->index('key');
            $table->index('group');
            $table->index(['key', 'language_id']);
            $table->index(['language_id', 'group']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
