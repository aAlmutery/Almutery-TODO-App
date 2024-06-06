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
        Schema::create('revision_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('role_name');
            $table->text('was');
            $table->text('new');
            $table->foreignId('task_id')->constrained(table: 'tasks')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('created_by')->constrained(table: 'users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revision_task');
    }
};
