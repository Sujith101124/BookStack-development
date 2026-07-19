<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePageReadingProgressTable extends Migration
{
    public function up(): void
    {
        Schema::create('page_reading_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('page_id');
            $table->unsignedTinyInteger('progress_percentage')->default(0);
            $table->unsignedInteger('scroll_position')->default(0);
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('visit_count')->default(0);
            $table->timestamp('first_opened_at')->nullable();
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
            $table->index(['user_id', 'last_opened_at']);
            $table->index(['user_id', 'page_id'], 'page_reading_progress_user_page_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_reading_progress');
    }
}
