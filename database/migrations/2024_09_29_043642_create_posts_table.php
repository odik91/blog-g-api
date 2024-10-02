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
        Schema::create('posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('category_id');
            $table->uuid('subcategory_id');
            $table->string('title');
            $table->string('slug');
            $table->string('image');
            $table->longText('meta_description', 300);
            $table->longText('meta_keyword', 300);
            $table->longText('seo_title', 300);
            $table->longText('content');
            $table->boolean('is_active');
            $table->softDeletes();
            $table->timestamps();

            // relation
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

            $table->foreign('subcategory_id')->references('id')->on('subcategories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};