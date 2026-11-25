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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('name', 200);
            $table->string('slug', 220)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('status', 20)->default('active'); // active, archived
            $table->timestamps();

            $table->index('owner_id');
            $table->index('status');
        });

        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('folders')->onDelete('cascade');
            $table->string('name', 200);
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index('project_id');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folders');
        Schema::dropIfExists('projects');
    }
};
