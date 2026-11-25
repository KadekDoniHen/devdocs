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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->timestamps();
        });

        Schema::create('document_tags', function (Blueprint $table) {
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->primary(['document_id', 'tag_id']);
        });

        Schema::create('api_endpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->string('name', 200);
            $table->string('method', 10); // GET, POST, PUT, DELETE, PATCH, OPTIONS
            $table->string('path', 500);
            $table->text('description')->nullable();
            $table->string('authentication', 100)->nullable();
            $table->jsonb('headers')->nullable();
            $table->jsonb('parameters')->nullable();
            $table->jsonb('request_body')->nullable();
            $table->jsonb('responses')->nullable();
            $table->timestamps();

            $table->index('document_id');
            $table->index(['method', 'path']);
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->nullable()->constrained('documents')->onDelete('cascade');
            $table->foreignId('uploader_id')->constrained('users')->onDelete('cascade');
            $table->string('original_name', 255);
            $table->string('storage_path', 500);
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamps();

            $table->index('document_id');
            $table->index('uploader_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('api_endpoints');
        Schema::dropIfExists('document_tags');
        Schema::dropIfExists('tags');
    }
};
