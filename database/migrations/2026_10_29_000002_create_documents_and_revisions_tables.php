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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('folder_id')->nullable()->constrained('folders')->onDelete('set null');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('last_editor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title', 250);
            $table->string('slug', 280);
            $table->text('summary')->nullable();
            $table->jsonb('content')->nullable();
            $table->longText('content_markdown')->nullable();
            $table->string('status', 20)->default('draft'); // draft, published, archived
            $table->integer('version')->default(1);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('project_id');
            $table->index('folder_id');
            $table->index('status');
            $table->index(['project_id', 'slug']);
        });

        Schema::create('document_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('editor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('revision_number');
            $table->string('title', 250);
            $table->jsonb('content_snapshot')->nullable();
            $table->longText('content_markdown')->nullable();
            $table->text('change_summary')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['document_id', 'revision_number']);
            $table->index('document_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_revisions');
        Schema::dropIfExists('documents');
    }
};
