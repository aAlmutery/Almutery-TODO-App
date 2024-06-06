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
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('status')->default(1)->constrained(table: 'status_list')->onUpdate('cascade')->onDelete('cascade');

            $table->foreignId('assign_to')->nullable()->constrained(table: 'users')->onUpdate('cascade')->onDelete('cascade');

            $table->foreignId('parent')->nullable()->constrained(table: 'tasks')->onUpdate('cascade')->onDelete('cascade');

            $table->foreignId('created_by')->constrained(table: 'users')->onUpdate('cascade')->onDelete('cascade');

            $table->foreignId('updated_by')->nullable()->constrained(table: 'users')->onUpdate('cascade')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    
};
